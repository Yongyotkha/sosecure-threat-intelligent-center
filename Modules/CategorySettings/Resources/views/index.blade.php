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
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right"
                    data-rel="tooltip" title="@langapp('export') CSV">
                    @icon('solid/download') CSV
                </a>

                {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }}
                pull-right" data-toggle="modal" data-target="#create_key_modal">
                @icon('solid/plus') @langapp('create')
                    </a> --}}

                    @if(isAdmin() || can('settings'))
                        <a href="{{ route('categorysettings.create') }}"
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
                                            <input name="select_all" value="1" onclick="go(); return false;" id="select-all" type="checkbox" />
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
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal Gen Category -->
    {{-- <div class="modal fade" id="create_key_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Category</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Key <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" class="form-control" name="generate_key" value="" readonly>
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-info">Gen</button>  
                            </span>
                        </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status </label>
                        <div class="col-lg-6">
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
    </div> --}}

</section>


@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')

    @include('stacks.js.datatables')

    <script>
        $(function () {

   


            var table = $('#table-category-template').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('categorysettings.data') !!}',
                    data: ""
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


        // function change_category_active(category_id,category_active) {
        //             // var form = $("#frm-category").serialize();
        //             axios.post('{{ route('categorysettings.bulk.delete') }}', category_id)
        //                 .then(function (response) {
        //                     toastr.warning(response.data.message, '@langapp('response_status')');
        //                     window.location.href = response.data.redirect;
        //                 })
        //                 .catch(function (error) {
        //                     var errors = error.response.data.errors;
        //                     var errorsHtml = '';
        //                     $.each(errors, function (key, value) {
        //                         errorsHtml += '<li>' + value[0] + '</li>';
        //                     });
        //                     toastr.error(errorsHtml, '@langapp('response_status') ');
        //                 });
        // }


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