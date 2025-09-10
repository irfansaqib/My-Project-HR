@extends('layouts.admin')
@section('title', 'Public Holidays')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Manage Public Holidays</h3>
        <a href="{{ route('holidays.create') }}" class="btn btn-primary">Add New Holiday</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Holiday Title</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $holiday)
                        <tr>
                            <td>{{ $holiday->date->format('d M, Y') }}</td>
                            <td>{{ $holiday->title }}</td>
                            <td>
                                <a href="{{ route('holidays.edit', $holiday->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No holidays found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $holidays->links() }}
        </div>
    </div>
</div>
@endsection