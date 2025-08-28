@extends('layouts.admin')
@section('title', 'Manage Departments')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Departments List</h3>
        <a href="{{ route('departments.create') }}" class="btn btn-primary float-right">Add New</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th style="width: 150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td>
                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-xs btn-warning">Edit</a>
                        <form method="POST" action="{{ route('departments.destroy', $department) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">No departments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection