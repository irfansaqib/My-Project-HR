@extends('layouts.admin')
@section('title', 'Work Shifts')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Manage Work Shifts</h3>
        <a href="{{ route('shifts.create') }}" class="btn btn-primary">Create New Shift</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Shift Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Grace Period</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td>{{ $shift->shift_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                            <td>{{ $shift->grace_period_in_minutes }} minutes</td>
                            <td>
                                <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this shift?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No shifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $shifts->links() }}
        </div>
    </div>
</div>
@endsection