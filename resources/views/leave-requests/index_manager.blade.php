@extends('layouts.admin')
@section('title', 'Leave Approvals')
@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">Leave Approval Queue</h3></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Submission Date</th>
                            <th>Emp. Number</th>
                            <th>Employee Name</th>
                            <th>Designation</th>
                            <th>Leave Type</th>
                            <th>Leave Dates</th>
                            <th>Status</th>
                            <th style="width: 200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $request)
                        <tr class="{{ $request->leave_type == 'extra' ? 'table-warning' : '' }}">
                            <td>{{ $request->created_at->format('d M, Y') }}</td>
                            <td>{{ $request->employee->employee_number ?? 'N/A' }}</td>
                            <td>{{ $request->employee->name ?? 'N/A' }}</td>
                            <td>{{ $request->employee->designation ?? 'N/A' }}</td>
                            <td>
                                {{ ucfirst($request->leave_type) }}
                                @if($request->leave_type == 'extra') <span class="badge badge-danger">Extra</span> @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('d M, Y') }}</td>
                            <td>
                                @if($request->status == 'approved') <span class="badge badge-success">Approved</span>
                                @elseif($request->status == 'rejected') <span class="badge badge-danger">Rejected</span>
                                @else <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('leave-requests.show', $request) }}" class="btn btn-xs btn-info">View</a>
                                @if($request->status == 'pending')
                                    <form method="POST" action="{{ route('leave-requests.approve', $request) }}" class="ml-1" style="display:inline;">@csrf @method('PATCH') <button type="submit" class="btn btn-xs btn-success">Approve</button></form>
                                    <form method="POST" action="{{ route('leave-requests.reject', $request) }}" class="ml-1" style="display:inline;">@csrf @method('PATCH') <button type="submit" class="btn btn-xs btn-danger">Reject</button></form>
                                @endif
                                @if($request->status == 'approved' && $request->leave_type == 'extra')
                                    <a href="{{ route('leave-requests.print', $request) }}" class="btn btn-xs btn-secondary ml-1" target="_blank">Print</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">There are no leave requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection