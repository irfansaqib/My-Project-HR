@extends('layouts.admin')
@section('title', 'Attendance Report')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Attendance Report</h3>
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form action="{{ route('reports.attendance') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-control selectpicker" data-live-search="true">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="shift_id">Shift</label>
                    <select name="shift_id" id="shift_id" class="form-control">
                        <option value="">All Shifts</option>
                         @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('reports.attendance') }}" class="btn btn-secondary">Reset</a>
        </form>

        {{-- Results Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M, Y') }}</td>
                            <td>{{ $attendance->employee->name }}</td>
                            <td>
                                <span class="badge 
                                    @if($attendance->status == 'present') badge-success 
                                    @elseif($attendance->status == 'late') badge-warning 
                                    @elseif($attendance->status == 'half-day') badge-info 
                                    @elseif($attendance->status == 'absent') badge-danger 
                                    @else badge-secondary @endif">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                </span>
                            </td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}</td>
                            {{-- ✅ DEFINITIVE FIX: Use {!! !!} to render the intelligent status messages from the model. --}}
                            <td>{!! $attendance->work_duration !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No attendance records found for the selected criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
@endpush

