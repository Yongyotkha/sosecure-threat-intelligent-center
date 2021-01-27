@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }}
            btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('settings') > Categorys</div>


            {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }}
            pull-right" data-toggle="modal" data-target="#create_key_modal">
            @icon('solid/plus') @langapp('create')
            </a> --}}



            @can('users_delete')
            <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger m-xs  pull-right"
                value="bulk-delete" disabled>
                <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt')
                    @langapp('delete')</span>
            </button>
            @endcan

            @if(isAdmin() || can('settings'))
            <a href="{{ route('categorysettings.create') }}"
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
                            <i class="fas fa-table"></i> Table Categorys
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <form id="frm-category" method="POST">
                        <div class="table-responsive">
                            @php
                            // dd(lastMonth());
                            @endphp
                            <table class="table table-striped" id="table-category-template">
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
                                        <th>@langapp('name')</th>
                                        <th>@langapp('status')</th>
                                        <th class="no-sort">Action</th>

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

    <div class="modal" id="delete_categorys_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_categorys_submit"
                        onclick="delete_categorys_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
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

<script>
    var categorySettings_id = [];

        $('#table-category-template').on('click', '.select-chk', function () {
            if ($(this).is(':checked')) {

                $('#btn-change-status').prop("disabled", false);
            } else {
                
                if ($('.select-chk').filter(':checked').length < 1){

                    $('#btn-change-status').attr('disabled',true);
                }
            }
        });

        $('#table-category-template').on('click', '.categorySettings_id', function () {
            if ($(this).is(':checked')) {

                
                $('#btn-change-status').prop("disabled", false);
            } else {
                if ($('.categorySettings_id').filter(':checked').length < 1){
                    
                    $('#btn-change-status').attr('disabled',true);
                }
            }
        });
        $(function () {




            var table = $('#table-category-template').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                "dom": '<B><"d-flex d-inline-flex justify-content-between"lf>rt<"bottom"ip><"clear">',
                ajax: {
                    url: '{!! route('categorysettings.data') !!}',
                    data: ({
                        
                    }),
                    type: "POST",
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
                        data: 'id',
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
                        data: 'status',
                        name: 'active',
                        className:'w-25',
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        sortable: false,
                        className:'w-80 no-wrap',
                    }
                ]
            });








        });

        function del_cate_select(cate_id) {
            axios.post('{{ route('categorysettings.bulk.delete') }}', {checked: cate_id})
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

    $( "#btn-change-status" ).click(function() {
        categorySettings_id = [];
        $('#delete_categorys_modal').modal('show');
    });
    
    function delete_categorys_select_confirm(){

        $('.categorySettings_id:checked').each(function () {
                categorySettings_id.push(this.value);
                
        });
        $.ajax({
            type:"POST",
            url:"{{ route('categorysettings.change_delete') }}",
            data:{id: categorySettings_id},
            beforeSend: function(){
                $('.delete_categorys_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_categorys_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                $('.delete_categorys_submit').prop("disabled", true);
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






        function change_category_active (category_id) {

      			$.ajax({
      				type:"POST",
                url:"{{ route('categorysettings.change_status') }}",
                data:{category_id:category_id},
                beforeSend: function(){
                },
        				success:function(response) {
                            console.log(response);

                            toastr.warning(response.data.message, '@langapp('response_status')');
                            window.location.href = response.data.redirect;

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