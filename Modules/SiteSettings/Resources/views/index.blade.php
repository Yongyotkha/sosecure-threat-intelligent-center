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


            {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }}
            pull-right" data-toggle="modal" data-target="#create_key_modal">
            @icon('solid/plus') @langapp('create')
            </a> --}}

            @can('users_delete')
            <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs pull-right" value="bulk-delete"
                disabled>
                <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                    @langapp('delete')</span>
            </button>
            @endcan

            @if(isAdmin() || can('settings'))
            <a href="{{ route('sitesettings.create') }}"
                class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-toggle="ajaxModal">
                @icon('solid/plus') @langapp('create')
            </a>
            @endcan



        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Site
                        </div>
                    </div>
                </header>
                <div class="panel-body">
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
                                                <input name="select_all" value="1" id="select-all" type="checkbox"
                                                    class="select-chk" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th class="">No.</th>
                                        <th>@langapp('logo')</th>
                                        <th>@langapp('name')</th>
                                        <th>Categorys</th>
                                        <th>Assets Use</th>
                                        <th>Start Date</th>
                                        <th>Exprie Date</th>
                                        <th>@langapp('status')</th>
                                        <th class="no-sort" width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal" id="delete_site_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_site_submit"
                        onclick="delete_site_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>



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
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
                ajax: {
                    url: '{!! route('sitesettings.data') !!}',
                    data: ({
                        
                    }),
                    type: "POST",
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
                        name: 'categorys',
                        searchable: false
                    },
                    {
                        data: 'assets_use',
                        name: 'assets_use',
                        searchable: false
                    },
                    {
                        data: 'start_active',
                        name: 'start_active'
                    },
                    {
                        data: 'end_active',
                        name: 'end_active'
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
                        className: 'w-50 nowrap'
                    }
                ]
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

        $('#table-site-template').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-site-template').on('click', '.site_settings_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.site_settings_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.site_settings_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });  

    let del_val = [];
    $( "#btn_del_select" ).click(function() {
        del_val = [];
        $('#delete_site_modal').modal('show');
    });
    
    function delete_site_select_confirm(){

        $('.site_settings_id:checked').each(function () {
            del_val.push(this.value);
                
        });
        $.ajax({
            type:"POST",
            url:"{{ route('sitesettings.sitesettings_delete') }}",
            data:{id: del_val},
            beforeSend: function(){
                $('.delete_site_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_site_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                $('.delete_site_submit').prop("disabled", true);
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
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
</script>
@endpush
@endsection