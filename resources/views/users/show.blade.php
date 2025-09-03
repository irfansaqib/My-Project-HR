@extends('layouts.admin')
@section('title', 'View User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Details: <strong>{{ $user->name }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to List</a>
            @can('user-edit')
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit User</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>User Type (Role):</strong> 
                    @foreach($user->roles as $role)
                        <span class="badge badge-info">{{ $role->name }}</span>
                    @endforeach
                </p>
                <p><strong>Linked Employee:</strong> 
                    @if($user->employee)
                        <a href="{{ route('employees.show', $user->employee) }}">{{ $user->employee->name }}</a>
                    @else
                        <span class="text-muted">No employee linked.</span>
                    @endif
                </p>
            </div>
        </div>

        @if($user->roles->first()->name == 'User' && $user->permissions->count() > 0)
        <hr>
        <h5 class="mt-4">Assigned Permissions</h5>
        <div class="row">
            @foreach($permissions as $module => $permissionGroup)
                <div class="col-md-4">
                    <h6>{{ ucfirst(str_replace('-', ' ', $module)) }}</h6>
                    <ul>
                        @foreach($permissionGroup as $permission)
                            @if($user->hasPermissionTo($permission->name))
                            <li>{{ ucwords(str_replace('-', ' ', $permission->name)) }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection