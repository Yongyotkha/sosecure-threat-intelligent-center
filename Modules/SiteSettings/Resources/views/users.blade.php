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
                                <a href="{{route('sitesettings.edit', ['id' => 2])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li >
                                <a href="{{route('systemsetting.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('datasettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Data Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('userssettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
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
                    <div class="bc-head">Users</div>
                    <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        @icon('solid/plus') @langapp('create')
                    </a>
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
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html">

    </a>
</section>


@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datatables')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')

<script>
    $(function() {
        $('#table-users-template').DataTable({
            processing: true,
            order: [[ 0, "desc" ]],
        });
    });
</script>

@endpush

@endsection