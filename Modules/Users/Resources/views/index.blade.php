@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">

        <section class="vbox">

            <header class="header panel-heading bg-white b-b b-light">
                @admin
                {{-- <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }}
                pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
                </a> --}}
                @endadmin

                @if(!empty(get_role_custom()))
                {{-- // var_dump(get_role_custom()['superadmin']);
                    // var_dump(get_role_custom()['site_admin']); --}}
                    @if(@get_role_custom()['superadmin'] == 1)

                        @can('users_delete')
                        <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger pull-right" value="bulk-delete"
                            data-rel="tooltip" title="Are you sure?" data-placement="bottom" disabled>
                            @icon('solid/trash-alt') @langapp('delete')
                        </button>
                        @endcan
        
        
        
                        @if(isAdmin() || can('announcements_create'))
                        <a href="{{ route('announcements.index') }}" style="display: none"
                            class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-rel="tooltip"
                            title="@langapp('announcements')" data-placement="bottom">
                            @icon('solid/bullhorn') @langapp('announcements')
                        </a>
                        @endif
        
                        @can('users_create')
                        <a href="{{ route('users.create') }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right"
                            data-toggle="ajaxModal">
                            @icon('solid/plus') @langapp('create')
                        </a>
                        @endcan

                        <div class="btn-group" style="padding-right: 2px;width: 120px;">
                            <select name="role" id="role" class="select2-option form-control select-site"
                                style="min-width: 120px">
                                <option value="">All Role</option>
                                <option value="99">Admin Support</option>
                                @foreach (Role::get() as $role)
                                <option value="{{@$role->id}}">{{@$role->name}}
                                </option>
                                @endforeach
        
                            </select>
                        </div>

                    @endif
                @endif



                <div class="pull-right" style="padding-top: 10px; width: 300px;">
                    <select name="site" id="site" class="select2-option form-control select-site"
                        style="min-width: 300px">
                        <option value="">All Site</option>
                        @if ($SiteSettings)

                        @foreach ($SiteSettings as $SiteSettings)
                        <option value="{{@$SiteSettings->id}}">{{@$SiteSettings->name}}
                        </option>
                        @endforeach

                        @endif
                    </select>
                </div>

                @if(!empty(get_role_custom()))
                {{-- // var_dump(get_role_custom()['superadmin']);
                    // var_dump(get_role_custom()['site_admin']); --}}
                    @if(@get_role_custom()['superadmin'] == 1)
                        @can('roles_view_all')
                        <a href="{{  route('users.roles')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                            @icon('solid/user-secret') @langapp('roles') </a>
        
                        <a href="{{  route('users.perm')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                            @icon('solid/shield-alt') @langapp('permissions')</a>
        
                        @endcan
                    @endif
                @endif
            </header>



            <section class="scrollable wrapper">



                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table Users
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">

                        <form id="frm-user" method="POST">

                            <div class="table-responsive">

                                <table class="table table-striped" id="users-table">
                                    <thead>
                                        <tr>
                                            <th class="no-sort">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox"
                                                        class="" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th class="">@langapp('name') </th>
                                            <th class="">@langapp('email') </th>
                                            <th class=" ">@langapp('job_title') </th>
                                            <th class=" "> Site Name </th>
                                            <th class="">@langapp('mobile') </th>
                                            <th class="">@langapp('city') </th>
                                            <th class="col-date">@langapp('date') </th>
                                        </tr>
                                    </thead>

                                </table>
                            </div>




                        </form>
                    </div>




                </section>
            </section>




        </section>

    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal " id="delete_user_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_domain_submit"
                        onclick="delete_user_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
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

<script>
    $(function() {

        data_table();

        $("#frm-user button").click(function(ev){
            ev.preventDefault();
            if($(this).attr("value") == "bulk-delete"){
            var form = $("#frm-user").serialize();
            axios.post('{{ route('users.bulk.delete') }}', form)
                .then(function (response) {
                    toastr.warning(response.data.message, '@langapp('response_status') ');
                    window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                var errors = error.response.data.errors;
                var errorsHtml= '';
                $.each( errors, function( key, value ) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                });
            }
        
        });

    });




    function data_table(){
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('users.data') !!}',
                data: {
                    "filter": '{{ $filter }}',
                    "site" : site,
                    "role" :role,
                }
            },
            order: [[ 7, "desc" ]],
            columns: [
                { data: 'chk', name: 'chk', orderable: false, searchable: false, sortable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'job_title', name: 'profile.job_title' },
                { data: 'site_name', name: 'site_name' },
                { data: 'mobile', name: 'profile.mobile' },
                { data: 'city', name: 'profile.city' },
                { data: 'created_at', name: 'created_at' }
            ]
        });
    }

    $('#users-table').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#users-table').on('click', '.user_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.user_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.user_id').filter(':checked').length < 1){
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });


    var site =null;
    var role =null;
    
    $('#site').on('change', function() {
        site = this.value;
        data_table();
    });
    $('#role').on('change', function() {
        role = this.value;
        data_table();

    });

    let del_user_select = [];

    $("#btn_del_select").click(function(){
        del_user_select = [];
        $('#delete_user_modal').modal('show');
    });

    function delete_user_select_confirm(){
        
        $(".user_id").each(function(){
            if($(this).is(":checked")) {
                del_user_select.push($(this).val());
            }
        });

        $.ajax({
            type:"POST",
            url:"{{ route('users.del_user') }}",
            data:{
                id:del_user_select
            },
            beforeSend: function(){
                $('.delete_domain_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                if(response.message==''){
                    $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                    toastr.error('Cannot delete user account', '@langapp('response_status')');
                }else{
                    $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                    toastr.success(response.message, '@langapp('response_status')');
                    window.location.href = response.redirect;
                }

            },
            error: function (error){
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }
</script>
@endpush

@endsection