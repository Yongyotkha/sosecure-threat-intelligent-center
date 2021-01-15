@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                        <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                        <p class="h3 text-elipse-setting">Name Domain</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">Settings > Keyword </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="{{route('keyword.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="ajaxModal">
                        @icon('solid/plus') @langapp('add')
                    </a>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Keywords
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_cve_assets">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>@langapp('keyword')</th>
                                            <th>@langapp('type')</th>
                                            <th style="width: 150px;">Last @langapp('update')</th>
                                            <th style="width: 100px;">@langapp('status')</th>
                                            <th class="no-sort" style="width: 100px;">@langapp('action')</th>
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
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal fade" id="create_assets_vulnerability" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Asset</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Site</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" placeholder="Site">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status</label>
                        <div class="col-lg-9">
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
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
<script>
    $(function() {

        $('#table_cve_assets').on('click', '.select-chk', function () {
                if ($(this).is(':checked')) {

                    $('#btn-change-status').prop("disabled", false);
                } else {
                    
                    if ($('.select-chk').filter(':checked').length < 1){

                        $('#btn-change-status').attr('disabled',true);
                    }
                }
            });

           

            $('#table_cve_assets').on('click', '.keyword_id', function () {
                if ($(this).is(':checked')) {
                
                    
                    $('#btn-change-status').prop("disabled", false);
                } else {
                    if ($('.keyword_id').filter(':checked').length < 1){

                        $('#btn-change-status').attr('disabled',true);
                    }
                }
        });

        var keyword_id = [];
     
        $("#btn-change-status").click(function() {
            $('.keyword_id:checked').each(function () {
                keyword_id.push(this.value);
            });
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type:"POST",
                        url:"{{ route('KeywordsController.delete_checked') }}",
                        data:{id: keyword_id},
                        beforeSend: function(){
                            loading('load');
                        },
                        success:function(response) {
                            loading('stop_load');
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
            })
        });


        var table = $('#table_cve_assets').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('KeywordsController.data') !!}',
                data: {
                    "site_code":'{{ Request::segment(3) }}'
                },
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
                    className: 'w-10'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'last_update',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
                
            ]
        });
    });

    function change_keyword_active(code) {
        let checkState = $("#keyword_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('keyword.change_status')}}', {
            active: checkState,
            code: code,
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
        });
    }

</script>
@endpush
@endsection