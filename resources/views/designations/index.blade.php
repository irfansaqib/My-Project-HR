@extends('layouts.admin')

@section('title', 'Manage Designations')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Designations List</h3>
        <a href="{{ route('designations.create') }}" class="btn btn-primary float-right">Add New</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>Designation Name</th>
                    <th style="width: 150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $designation)
                <tr>
                    <td>{{ $designation->name }}</td>
                    <td>
                        <a href="{{ route('designations.edit', $designation) }}" class="btn btn-xs btn-warning">Edit</a>
                        <form method="POST" action="{{ route('designations.destroy', $designation) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this designation?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">No designations found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection