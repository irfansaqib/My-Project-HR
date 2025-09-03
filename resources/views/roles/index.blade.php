@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Roles List</h3>
        @can('role-create')
            <a href="{{ route('roles.create') }}" class="btn btn-primary float-right">Add New Role</a>
        @endcan
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th style="width: 150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>
                            @foreach ($role->permissions->take(5) as $permission)
                                <span class="badge badge-secondary">{{ $permission->name }}</span>
                            @endforeach
                            @if ($role->permissions->count() > 5)
                                <span class="badge badge-light">+ {{ $role->permissions->count() - 5 }} more</span>
                            @endif
                        </td>
                        <td>
                            @can('role-view')
                                <a class="btn btn-info btn-xs" href="{{ route('roles.show', $role->id) }}">View</a>
                            @endcan
                            {{-- UPDATED: Hide actions for Admin as well as Owner --}}
                            @if($role->name != 'Admin' && $role->name != 'Owner')
                                @can('role-edit')
                                    <a class="btn btn-warning btn-xs" href="{{ route('roles.edit', $role->id) }}">Edit</a>
                                @endcan
                                @can('role-delete')
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                    </form>
                                @endcan
                            @else
                                <span class="text-muted text-sm">Actions Disabled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection