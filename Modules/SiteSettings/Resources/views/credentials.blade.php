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
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs"
                        style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; Credentials </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right"
                    data-rel="tooltip" title="@langapp('export') CSV">
                    @icon('solid/download') CSV
                    </a> --}}
                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right"
                        value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    <button class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        id="add_credentials" data-target="#add-credentials">
                        <span>@icon('solid/plus') Add</span>
                    </button>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Credentials
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table_credentials">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox"
                                                        class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Name</th>
                                            <th>User</th>
                                            <th>Password</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
    </section>

    <div class="modal in fixed-left" id="add-credentials" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();"
                            datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Add Credentials
                    </h4>
                </div>
                <form id='add_credentials_click' method="POST">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">User <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" name="" id="user" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Password <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="password" name="" id="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-9">
                                <label class="switch">
                                    <input type="checkbox" id="status" name="status" checked value="1">
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
                        <button type="submit" class="btn btn-info btn-rounded">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="modal in fixed-left" id="delete_credentials" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                            title="Fullscreen" data-placement="right"></i>
                        @langapp('delete')
                    </h4>
                </div>
                <form action="">
                    <div class="modal-body">
                        <p class="text-danger">@langapp('delete_warning') </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="button" onclick="delete_credentials_save()" data-dismiss="modal"
                            class="btn btn-info btn-rounded">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="modal" id="delete_credentials" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')  </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit" onclick="delete_credentials_save()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
<script>
    var check = 1;
    var name = null;
    var user = null;
    var password = null;
    var credentials_id_delete_change = [];
    var credentials_id_delete = null;

    $(function () {
        $('#table_credentials').DataTable({
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
        });

        table_credentials();
    });

    $("#status").on('change', function() {
        if ($(this).is(':checked')) {
            $(this).attr('value', '1');
        } else {
            $(this).attr('value', '0');
        }
        
        check= $('#status').val();
  
    });

    $("#add_credentials").on('click', function() {

        name = null;
        reference = null;
        user = null;
        password = null;
        check=1;
        



        document.getElementById("status").checked = true;
        $("#name").val('');
        $("#user").val('');
        $("#password").val('');

    });

    $("#add_credentials_click").submit(function(e) {
        name = $('#name').val();
        user = $('#user').val();
        password = $('#password').val();
        
        

        $.ajax({
            type:"POST",
            url:"{{ route('credentials.create_credentials') }}",
            data:{
                check:Number(check),
                name:name,
                password:password,
                user:user,
                site:@json($siteSettings->id),
                code:@json($siteSettings->code),
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                
                loading('stop_load');
                if(response.message!=''){
                    window.$('#add-credentials').modal('hide');
                    toastr.success(response.message, '@langapp('response_status')');
                    $('#table_credentials').DataTable().ajax.reload();
                }else{

                    toastr.error('There is already this name in the system.', '@langapp('response_status')');

                }
                
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }

        });
        e.preventDefault();
    });

    function add_credentials_click() {


    }
    
    $('#table_credentials').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn-change-status,#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn-change-status,#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table_credentials').on('click', '.credentials_id', function () {
        if ($(this).is(':checked')) {
            $('#btn-change-status,#btn_del_select').prop("disabled", false);
            {{--if($('.credentials_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.credentials_id').filter(':checked').length < 1){
                $('#btn-change-status,#btn_del_select').attr('disabled',true);
            }
        }
    });       

    function table_credentials() {
    
        $('#table_credentials').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: "POST",
                url: '{!! route('credentials.table_credentials') !!}',
                data: ({

                        site: @json($siteSettings->id),

                }),
            },
        
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
            
                
            },
            createdRow: function ( row, data, index ) {
                $(row).attr('id', 'tr' + data.id);
            },
            columns: [
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'chk',
                    className: 'w-10'
                },
                {
        
                    data: 'name',
                    name: 'name',
            
                    
                },
                {
                    data: 'user',
                    name: 'user',
            

                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,

                    data: 'password',
                    name: 'password',
        
                
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,

                    data: 'reference',
                    name: 'reference',
        
                
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
    
                    data: 'status',
                    name: 'status',
                    className: 'w-10 text-center'
        
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'action',
                    name: 'action',
                    className: 'w-10 text-center no-wrap'

                },
            ],
            columnDefs: [

                {
                    targets: 0,

                    width: '1px',
                    render: function (data, type, full, meta) {

                        return  '<label><input type="checkbox" name="credentials_id" class="credentials_id" value="' + full.id + '"><span class="label-text"></span></label>';
                    },
                },
                {
                    targets: 1,

                    width: '10px',
                    render: function (data, type, full, meta) {

                        return full.name;
                    },
                },
                {
                    targets: 2,
                    width: '10px',
                    render: function (data, type, full, meta) {
                        return full.user;
                            
                    },
                },
                {
                    targets: 3,
                    width: '10px',
                    
                    render: function (data, type, full, meta) {
                    
            
                        return '<span style="-webkit-text-security: disc;">'+full.password+'</span>';

                    },
                },
                {
                    targets: 4,
                    width: '10px',
                    
                    render: function (data, type, full, meta) {
                        html='';
                        
                        if(full.get_compromised_server){
                            if(full.get_compromised_server.length!=0){
                                for(var i=0;i<=full.get_compromised_server.length;i++){
                                    if(full.get_compromised_server[i]!=undefined){
                                        html+=''+full.get_compromised_server[i].ip+'<br>';
                                    }
                                    
                                }
                                
                                return html;
                            }else{
                            return '-';
                            }

                           
                        }else{
                            return '-';
                        }
                        
                        

                    },
                },
                {
                    targets: 5,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        
                        var checked_val = null;
                                    if (full.status == 1) {
                                        checked_val = 'checked';
                                    } else {
                                        checked_val = '';
                                    }
                            
                                return  '<label class="switch"><input type="checkbox" id="credentials_active_' +full.id+  '" onchange="credentials_active( '+full.id+')" '+checked_val+' name="active" value="1"><span class="slider round"></span></label>';

                            
                        
                    },
                },
                
                {
                    targets: 6,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        var html = '';
                        html =`<a href="${base_url}/Credentials/credentials_edit_modal/${full.code}" class="btn btn-{{get_option("theme_color") }} btn-xs" data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>`;
                        html +=`<a href="#" onclick="delete_credentials(${full.id})" class="btn btn-danger btn-xs"  data-toggle="modal" data-target="#delete_credentials"><i class="fas fa-trash-alt"></i></a>`;
                        return html;

                    },
                },

            ]
        
        });
    }

    function credentials_active(id) {

        let checkState = $("#credentials_active_" + id).is(":checked") ? 1 : 0;

        $.ajax({
            type:"POST",
            url:"{{ route('credentials.credentials_change_status') }}",
            data:{
                active: checkState,
                id: id,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                $('#table_credentials').DataTable().ajax.reload();
                {{--window.location.href = response.redirect;--}}
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }

        });
    {{--    axios.post('{{route('compromised_web_server.web_server_change_status')}}', {
            active: checkState,
            id: id,
            site:{!!json_encode($siteID)!!},
        }).then(function (response) {

            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });--}}
    }

    function delete_credentials(id){
        credentials_id_delete = id;
        credentials_id_delete_change=[];
     
    }

    function delete_credentials_save(){
        $.ajax({
            type:"POST",
            url:"{{ route('credentials.credentials_delete') }}",
            data:{
                id: credentials_id_delete,
                id_change: credentials_id_delete_change,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                window.$('#delete_credentials').modal('hide');
                $('#table_credentials').DataTable().ajax.reload();
                
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
                
        });

    }

    $("#btn_del_select").click(function() {
        credentials_id_delete_change=[];
        $('#delete_credentials').modal('show');
        $('.credentials_id:checked').each(function () {
            credentials_id_delete_change.push(this.value);     
        });
    });

    {{--$("#btn_del_select").click(function() {
        
        


       Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('credentials.credentials_delete') }}",
                    data:{
                        id_change: credentials_id_delete_change,
                        site:@json($siteSettings->code),
                    },
                    beforeSend: function(){
                        loading('load');
                    },
                    success:function(response) {
                        loading('stop_load');
                        credentials_id_delete_change=[];
                        toastr.success(response.message, '@langapp('response_status')');
                        $('#table_credentials').DataTable().ajax.reload();
                    },
                    error: function (error){
                        loading('stop_load');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
                
                });

            }
        })
    });--}}
     
       
  
</script>
@endpush
@endsection