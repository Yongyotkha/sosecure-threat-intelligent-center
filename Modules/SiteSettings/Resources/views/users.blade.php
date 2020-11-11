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
                                                <th class="hide"></th>
                                                <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th>@langapp('name')  </th>
                                                <th>@langapp('email')</th>
                                                <th>@langapp('role')   </th>
                                                <th>@langapp('status')   </th>
                                                <th>@langapp('update')   </th>
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

<script>
    $(document).ready(function () {
        $('#role').select2();
    });

    $(function() {
        $('#table-users-template').DataTable({
            processing: true,
            order: [[ 0, "desc" ]],
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


    function change_domain_active(code) {
        let checkState = $("#domain_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('domainsettings.change_status')}}', {
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
