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
                                <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('datasettings.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Permission & Config Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('userssettings.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li>
                                <a href="{{route('domain.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Assets
                                </a>
                            </li>
                            <li class="main-link">
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Data Leak
                                </a>
                                <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
                                    <li style="padding-left:2rem">
                                        <a href="{{route('keyword.index', ['id' => $siteSettings->code])}}">
                                            Keyboard Setting
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    News
                                </a>
                            </li>

                            <li class="main-link">
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Vulnerability
                                </a>
                                <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
                                    <li style="padding-left:2rem">
                                        <a href="{{route('vulsetting.vul_assets', ['id' => $siteSettings->code])}}">
                                            Assets
                                        </a>
                                    </li>
                                    <li style="padding-left:2rem">
                                        <a href="{{route('vulsetting.vul_logs', ['id' => $siteSettings->code])}}">
                                            Logs
                                        </a>
                                    </li>
                                </ul>
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
                    <div class="bc-head">Site Setting &gt; {{ $siteSettings->name }}</div>
                    <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <!-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create-new-user">
                        @icon('solid/plus') @langapp('create')
                    </a> -->
                    <a href="{{route('user.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-toggle="ajaxModal">@icon('solid/plus') @langapp('create')</a>
                    <button type="submit" id="button" class="btn btn-sm btn-{{ get_option('theme_color')  }}  pull-right m-xs">
                        <span>@icon('solid/user') @langapp('role')</span>
                    </button>
                </header>

                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/user') Users</header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table  class="table table-striped" id="table-users-template">
                                        <thead>
                                            <tr>
                                                {{-- <th class="hide"></th> --}}
                                                <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th class="">No.</th>
                                                <th>@langapp('name')  </th>
                                                <th>@langapp('email')</th>
                                                <th>@langapp('confirm')</th>
                                                <th>@langapp('role')   </th>
                                                <th>@langapp('status')   </th>
                                                <th>@langapp('lastupdate')   </th>
                                                <th>@langapp('action')   </th>
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

    <!-- Modal New User -->
    <div class="modal modal-slide" id="create-new-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">New User</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
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
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal modal-slide" id="support-password" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Support Password</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
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

</section>


@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datatables')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.menusub')

<script>
    $(document).ready(function () {
        $('#role').select2();
    });



    $(function () {

        var table = $('#table-users-template').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('user.data') !!}',
                data: {
                    "site_code":'{{ Request::segment(3) }}'
                }
            },
            order: [
                [0, "desc"]
            ],
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: "w-10",
                },
                {
                    data: 'no',
                    className: "w-15",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: 'name',
                    name: 'name',
                    className:'w-100',
                },
                {
                    data: 'email',
                    name: 'email',
                    className:'w-100',
                },
                {
                    data: 'confirm',
                    name: 'confirm',
                    className:'w-25 text-center',
                },
                {
                    data: 'role',
                    name: 'role',
                    className:'w-25',
                },
                {
                    data: 'status',
                    name: 'status',
                    className:'w-25',
                },
                {
                    data: 'lastupdate',
                    name: 'lastupdate',
                    className:'w-25',
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className:'w-80',
                }
            ]
        });

        let del_val = [];
        $("#btn_del_select").click(function(){
            del_val = [];
            $("input[type='checkbox'][name='checked']").each(function(){
                
                if($(this).is(":checked")) {
                    del_val.push($(this).val());
                    /* alert(3);*/
                }
            });
            console.log(del_val);

            if(del_val.length > 0) {
                del_cate_select(del_val);
            } else {
                toastr.warning('Please select atleast 1', '@langapp('response_status')');
            }
        });
    });


    function del_cate_select(id) {
        axios.post('{{ route('domainsettings.bulk.delete') }}', {checked: id})
        .then(function (response) {
            toastr.warning(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        })
        .catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
        });
    }


    function change_user_active(code) {
        let checkState = $("#user_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('user.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }
</script>


@endpush

@endsection
