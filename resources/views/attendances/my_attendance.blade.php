@extends('layouts.admin')
@section('title', 'My Attendance')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Attendance for {{ $month->format('F, Y') }}</h3>
            <div class="card-tools">
                <form action="{{ route('attendances.my') }}" method="GET" class="form-inline">
                    <input type="month" name="month" class="form-control form-control-sm mr-2" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4 text-center">
                <div class="col-3"><div class="callout callout-success"><h5>{{ $stats['present'] }}</h5><p>Present</p></div></div>
                <div class="col-3"><div class="callout callout-warning"><h5>{{ $stats['late'] }}</h5><p>Late</p></div></div>
                <div class="col-3"><div class="callout callout-info"><h5>{{ $stats['leaves'] }}</h5><p>Leaves</p></div></div>
                <div class="col-3"><div class="callout callout-danger"><h5>{{ $stats['absent'] }}</h5><p>Absent</p></div></div>
            </div>

            <table class="table table-bordered table-striped text-sm">
                <thead class="bg-light">
                    <tr><th>Date</th><th>Status</th><th>In</th><th>Out</th></tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    <tr>
                        <td>{{ $att->date->format('d M, Y (D)') }}</td>
                        <td>
                            <span class="badge badge-{{ $att->status == 'present' ? 'success' : ($att->status == 'absent' ? 'danger' : ($att->status == 'late' ? 'warning' : 'info')) }}">
                                {{ ucfirst($att->status) }}
                            </span>
                        </td>
                        <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '-' }}</td>
                        <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection