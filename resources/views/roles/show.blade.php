@extends('layouts.admin')
@section('title', 'View Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Role Details: <strong>{{ $role->name }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Back to List</a>
            @can('role-edit')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">Edit Role</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <h5 class="mt-4">Assigned Permissions</h5>
        <div class="row">
            @forelse($permissions as $module => $permissionGroup)
                <div class="col-md-4">
                    <h6>{{ ucfirst(str_replace('-', ' ', $module)) }}</h6>
                    <ul>
                        @foreach($permissionGroup as $permission)
                            <li>{{ ucwords(str_replace(['-view', '-create', '-edit', '-delete'], '', $permission->name)) }}</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="col-12"><p class="text-muted">No permissions assigned to this role.</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection