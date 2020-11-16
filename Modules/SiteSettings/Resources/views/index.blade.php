@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }}
            btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
                </a> --}}
                <div class="bc-head">@langapp('settings') > Site</div>
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right"
                    data-rel="tooltip" title="@langapp('export') CSV">
                    @icon('solid/download') CSV
                </a>

                {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }}
                pull-right" data-toggle="modal" data-target="#create_key_modal">
                @icon('solid/plus') @langapp('create')
                    </a> --}}

                    @if(isAdmin() || can('settings'))
                        <a href="{{ route('sitesettings.create') }}"
                            class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right"
                            data-toggle="ajaxModal">
                            @icon('solid/plus') @langapp('create')
                        </a>
                    @endcan

                    @can('users_delete')
                        <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs pull-right" value="bulk-delete">
                            <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                                @langapp('delete')</span>
                        </button>
                    @endcan

        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">

                <form id="frm-site" method="POST">
                    <div class="table-responsive">
                        @php
                            // dd(lastMonth());
                        @endphp
                        <table class="table table-striped" id="table-site-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" onclick="go(); return false;" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th class="">No.</th>
                                    <th>@langapp('logo')</th>
                                    <th>@langapp('name')</th>
                                    <th>Categorys</th>
                                    <th>@langapp('status')</th>
                                    <th class="no-sort" width="10%">Action</th> 
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </form>
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>



</section>


@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')

    @include('stacks.js.datatables')
    @include('stacks.js.defaultpic')

    <script>
        $(function () {
            var table = $('#table-site-template').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: '{!! route('sitesettings.data') !!}',
                    data: ""
                },
                columns: [
                    {
                        data: 'chk',
                        orderable: false,
                        searchable: false,
                        sortable: false,
                        className: 'w-10'
                    },
                    {
                        data: 'id',
                        className: 'w-15',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                    },
                    {
                        data: 'logo',
                        name: 'logo'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'categorys',
                        name: 'categorys'
                    },
                    {
                        data: 'status',
                        name: 'active',
                        className: 'w-25'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        sortable: false,
                        className: 'w-50'
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
                /*console.log(del_val);*/

                if(del_val.length > 0) {
                    del_site_select(del_val);
                } else {
                    toastr.warning('Please select atleast 1', '@langapp('response_status')');
                }

            });

        });

        function del_site_select(site_id) {
            axios.post('{{ route('sitesettings.bulk.delete') }}', {checked: site_id})
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

        function change_site_active(code) {
            let checkState = $("#site-active-" + code).is(":checked") ? 1 : 0;
            axios.post('{{route('sitesettings.change_status.settings')}}', {
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
