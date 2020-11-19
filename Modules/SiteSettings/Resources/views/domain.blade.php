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
                            <li>
                                <a href="{{route('userssettings.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('domain.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
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
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#add-domain">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}

                    <a href="{{route('domain.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-toggle="ajaxModal">@icon('solid/plus') @langapp('create')</a>
                </header>

                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">  
                            <section class="panel panel-default">

                                <form id="frm-domain" method="POST"> 
                                    <header class="panel-heading">@icon('solid/user') Domain</header>
                                    <div class="panel-body">
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
                                    </div>

                                </form>
                                {{-- <div class="panel-footer">
                                
                                </div> --}}
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
            ajax: {
                url: '{!! route('scans.data') !!}',
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
                    className: 'w-10'
                },
                {
                    data: 'domain',
                    name: 'domain'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-center'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center'
                },
                {
                    data: 'elements',
                    name: 'elements',
                    className: 'w-10 text-center'
                },
                {
                    data: 'progress',
                    name: 'progress',
                    className: 'w-10'
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
