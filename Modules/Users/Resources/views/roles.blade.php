@extends('layouts.app')
@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header panel-heading bg-white b-b b-light">

                <a href="{{ route('users.index') }}" class="btn btn-{{ get_option('theme_color') }} btn-sm">
                    @icon('solid/arrow-left')
                </a>

                @can('roles_create')
                <a href="{{ route('users.roles.create') }}" data-toggle="ajaxModal" class="btn btn-{{ get_option('theme_color') }} btn-sm pull-right">
                    @icon('solid/plus') @langapp('create')
                </a>
                @endcan
                
                @can('menu_users')
                <a href="{{ route('users.index') }}" class="btn btn-{{ get_option('theme_color') }} btn-sm pull-right">
                    @icon('solid/user-circle') @langapp('users')
                </a>
                @endcan

               

           
        
            </header>
            <section class="scrollable wrapper">

                <div class="alert alert-warning">
                    <h5>1. Create Role</h5>
                    <h5>2. Setting Permission menu</h5>
                 </div>

                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row d-flex-center">
                            <div class="col-md-12">
                                <i class="fas fa-table"></i> Table Role
                            </div>
                        </div>
                    </header>
                    <div class="panel-body" id="table-container">
                        <div class="table-responsive">
                            <table class="table table-striped" id="roles-table">
                                <thead>
                                    <tr>
                                        <th class="hide">ID</th>
                                        <th class="">@langapp('name')</th>
                                        <th class="">Guard</th>
                                        <th class="">Permission Menu</th>
                                        <th style="width: 120px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (Role::get() as $key => $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ ucfirst($role->name) }}</td>
                                        
                                        <td class="">
                                            {{ $role->guard_name }}
                                        </td>
                                        <td class="">
                                            <div class="d-il-block">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" name="" checked="" value="TRUE">
                                                    <span class="label-text" data-rel="tooltip" title="" data-original-title="">Dashboard</span></label>
                                                </div>
                                            </div>
                                            <div class="d-il-block">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" name="" checked="" value="TRUE">
                                                    <span class="label-text" data-rel="tooltip" title="" data-original-title="">Assets</span></label>
                                                </div>
                                            </div>
                                            <div class="d-il-block">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" name="" checked="" value="TRUE">
                                                    <span class="label-text" data-rel="tooltip" title="" data-original-title="">News</span></label>
                                                </div>
                                            </div>
                                            <div class="d-il-block">
                                                <div class="checkbox">
                                                    <label><input type="checkbox" name="" checked="" value="TRUE">
                                                    <span class="label-text" data-rel="tooltip" title="" data-original-title="">Indicators</span></label>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="nowrap">
                                            
                                            {{-- @if($role->id != 1 && $role->id != 2 && $role->id != 4 && $role->id != 5) --}}
                                            
                                            {{-- <a href="{{ route('users.roles.permission_custom', ['id' => $role->id]) }}" class="btn btn-{{ get_option('theme_color') }} btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/shield-alt')
                                            </a> --}}
                                            
                                            <a href="{{ route('users.roles.permission', ['id' => $role->id]) }}" class="btn btn-{{ get_option('theme_color') }} btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/shield-alt')
                                            </a>
                                            
                                            <a href="{{ route('users.roles.edit', ['id' => $role->id]) }}" class="btn btn-{{ get_option('theme_color') }} btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/pencil-alt')
                                            </a>
                                            <a href="{{ route('users.roles.delete', ['id' => $role->id]) }}" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/trash-alt')
                                            </a>
                                            {{-- @endif --}}
                                       
                                            
                                        </td>
                                        
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
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
<script>
$(function() {
var table = $('#roles-table').DataTable({
processing: true,
order: [[ 0, "asc" ]],
});
});
</script>
@endpush
@endsection