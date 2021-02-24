@extends('layouts.app')

<style>
    .w100px {
        width: 100px;
    }
</style>

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
                <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                    <div class="header-flex-overflow" style="height: 47px;">
                        <div class="fwb-16">
                            <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                            <span style="margin-top: 2px">
                                Site Setting &gt; Domain
                            </span>
                        </div>
    
                        <div class="ml-2 text-right">
                        
                            <a href="{{route('domain.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color') }}" data-toggle="ajaxModal">
                                <span data-rel="tooltip" title="Add" data-placement="top">@icon('solid/plus')</span>
                                <span class="hide-text">@langapp('add')</span>
                            </a>
    
                            <button type="button" onclick="delete_domain_select()" id="btn_del_select" class="btn btn-sm btn-danger"  disabled>
                                <span data-rel="tooltip" title="Delete" data-placement="top">@icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span></span>
                            </button>

                        </div>     
                    </div>
                </header>

                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">  
                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table Domain
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
                                    <form id="frm-domain" method="POST"> 
                                        <div class="table-responsive">
                                            <table  class="table table-striped" id="table-domain-template">
                                                <thead>
                                                    <tr>
                                                        {{-- <th class="hide"></th> --}}
                                                        <th class="no-sort">
                                                            <label>
                                                                <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                                <span class="label-text"></span>
                                                            </label>
                                                        </th>
                                                        {{-- <th class="">No.</th> --}}
                                                        <th>Name</th>
                                                        <th>Domain</th>
                                                        <th>Default</th>
                                                        <th>Started</th>
                                                        <th>Finished</th>
                                                        <th>Elements</th>
                                                        <th>Progress</th>
                                                        {{-- <th>@langapp('status')</th> --}}
                                                        <th class="no-sort" width="10%">@langapp('action')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
      
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html"></a>

    <div class="modal" id="delete_domain_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_domain_submit" onclick="delete_domain_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
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
{{-- @include('partial.ajaxify') --}}
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')

<?php 
    // $(function() {
    //     $('#table-domain-template').DataTable({
    //         processing: true,
    //         order: [[ 0, "desc" ]],
    //     });
    // });
?>

<script>
    $(document).ready(function () {
        $('#scan_interval').select2();
    });
    
    $('ul.role-group-sub').hide();
    function openrole(onck,id){
        $('#'+id).slideToggle(150);
    }

    $('#table-domain-template').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-domain-template').on('click', '.domain_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.domain_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.domain_id').filter(':checked').length < 1){
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });


    $(function () {

        var table = $('#table-domain-template').DataTable({
            processing: true,
            serverSide: true,
            
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('domainsettings.data') !!}',
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
                    data: 'domain',
                    name: 'domain'
                },
                {
                    data: 'domain_default',
                    name: 'domain_default',
                    className: 'w-10 text-center'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-center w100px no-wrap'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center w100px no-wrap'
                },
                {
                    data: 'elements',
                    name: 'elements',
                    className: 'w-10 text-center'
                },
                {
                    data: 'progress',
                    name: 'progress',
                    className: 'w100px'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
                
            ]
        });




    });
    let del_domain_select = [];
    function delete_domain_select(){
        del_domain_select = [];
        $('#delete_domain_modal').modal('show');
    }

    function delete_domain_select_confirm(){
        
        $("input[type='checkbox'][name='checked']").each(function(){
            if($(this).is(":checked")) {
                del_domain_select.push($(this).val());
            }
        });
        console.log(del_domain_select);
        $.ajax({
            type:"POST",
            url:"{{ route('domainsettings.del_domain_select') }}",
            data:{
                id:del_domain_select
            },
            beforeSend: function(){
                $('.delete_domain_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
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


    function change_category_active (id) {
        $.ajax({
            type:"POST",
            url:"{{ route('domainsettings.change_status') }}",
            data:{id:id},
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


    function change_domain_active(code) {
        let checkState = $("#domain_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('domainsettings.change_status')}}', {
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
