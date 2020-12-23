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
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                        <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                        <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; {{ $siteSettings->name }}</div>
                    <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#add-domain">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}

                    <a href="{{route('domain.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-toggle="ajaxModal">@icon('solid/plus') @langapp('create')</a>
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
                                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                                <span class="label-text"></span>
                                                            </label>
                                                        </th>
                                                        {{-- <th class="">No.</th> --}}
                                                        {{-- <th>Site Name</th> --}}
                                                        <th>Domain</th>
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
        </aside>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html"></a>



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
@include('stacks.js.hidesettings')

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


    $(function () {

        var table = $('#table-domain-template').DataTable({
            processing: true,
            serverSide: true,
            "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
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
                    data: 'domain',
                    name: 'domain'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-center w100px'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center w100px'
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
