@extends('layouts.admin')
@section('title', 'Leave Report')

@section('content')
<div class="row mb-3">
    {{-- STATS CARDS --}}
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Requests</span>
                <span class="info-box-number">{{ $totalRequests }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Approved</span>
                <span class="info-box-number">{{ $approvedCount }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending</span>
                <span class="info-box-number">{{ $pendingCount }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Rejected</span>
                <span class="info-box-number">{{ $rejectedCount }}</span>
            </div>
        </div>
    </div>
</div>

{{-- FILTERS --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.leave') }}">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control select2">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Leave Type</label>
                    <select name="leave_type_id" class="form-control">
                        <option value="">All Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
            </div>
            <div class="text-right mt-2">
                <button type="submit" name="export" value="excel" class="btn btn-success mr-1"><i class="fas fa-file-excel mr-1"></i> Export CSV</button>
                <a href="{{ route('reports.leave') }}" class="btn btn-secondary mr-1">Reset</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Generate Report</button>
            </div>
        </form>
    </div>
</div>

{{-- DATA TABLE --}}
<div class="card shadow-sm">
    <div class="card-header bg-white border-0">
        <h3 class="card-title font-weight-bold">Detailed Leave Records</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light">
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td class="font-weight-bold">{{ $leave->employee->name }}</td>
                        <td><span class="badge badge-light border">{{ $leave->leaveType->name ?? 'N/A' }}</span></td>
                        <td>{{ $leave->start_date->format('d M, Y') }}</td>
                        <td>{{ $leave->end_date->format('d M, Y') }}</td>
                        <td>{{ $leave->days }}</td>
                        <td><small>{{ Str::limit($leave->reason, 30) }}</small></td>
                        <td>
                            @if($leave->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($leave->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $leave->created_at->format('d M, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No leave records found matching your criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection