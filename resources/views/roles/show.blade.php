@extends('layouts.admin')
@section('title', 'View Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Role Details: <strong>{{ $role->name }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Back to List</a>
            @if($role->name != 'Admin' && $role->name != 'Owner')
                @can('role-edit')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">Edit Role</a>
                @endcan
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($role->permissions->isEmpty())
            <div class="alert alert-info">
                No permissions have been assigned to this role yet.
            </div>
        @else
            <h5 class="mb-3">Assigned Permissions</h5>
            <div class="row">
                @foreach($permissions as $module => $permissionGroup)
                    @if($permissionGroup->isNotEmpty())
                    <div class="col-md-4 mb-3">
                        <h6><strong>{{ ucwords(str_replace('-', ' ', $module)) }}</strong></h6>
                        <ul class="list-unstyled pl-3">
                            @foreach($permissionGroup as $permission)
                                <li><i class="fas fa-check-circle text-success mr-2"></i>{{ ucwords(str_replace('-', ' ', $permission->name)) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection