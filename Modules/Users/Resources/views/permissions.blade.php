@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header panel-heading b-b bg-white">
                <a href="{{ route('users.index') }}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left">
                 @icon('solid/arrow-left')
                </a>

                @can('roles_create')
                <a href="{{ route('users.perm.create') }}" data-toggle="ajaxModal" class="btn btn-{{ get_option('theme_color') }} btn-sm pull-right">
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
                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fas fa-table"></i> Table Permission
                            </div>
                        </div>
                    </header>
                    <div class="panel-body" id="table-container">
                        <div class="table-responsive">
                            <table class="table table-striped" id="permissions-table">
                                <thead>
                                    <tr>
                                        <th class="">@langapp('name')  </th>
                                        <th class="">@langapp('description')  </th>
                                        <th class="">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (Spatie\Permission\Models\Permission::getPermissions([]) as $key => $permission)
                                    <tr>
                                        <td>{{ humanize($permission->name) }}</td>
                                        <td class="text-muted">{{ $permission->description }}</td>
                                        <td class="nowrap">
                                            
                                            <a href="{{ route('users.perm.edit', ['id' => $permission->id]) }}" class="btn btn-{{ get_option('theme_color') }} btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/pencil-alt')
                                            </a>
                                            <a href="{{ route('users.perm.delete', ['id' => $permission->id]) }}" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                                                @icon('solid/trash-alt')
                                            </a>
                                            
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
    var table = $('#permissions-table').DataTable({
    processing: true,
    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
    order: [[ 0, "asc" ]],
});
});
</script>
@endpush
@endsection