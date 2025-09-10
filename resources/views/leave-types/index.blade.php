@extends('layouts.admin')
@section('title', 'Leave Types')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Manage Leave Types</h3>
        <a href="{{ route('leave-types.create') }}" class="btn btn-primary">Create New Leave Type</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leaveTypes as $leaveType)
                    <tr>
                        <td>{{ $leaveType->id }}</td>
                        <td>{{ $leaveType->name }}</td>
                        <td>
                            <a href="{{ route('leave-types.edit', $leaveType->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('leave-types.destroy', $leaveType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No leave types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $leaveTypes->links() }}
        </div>
    </div>
</div>
@endsection