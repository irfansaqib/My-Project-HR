@extends('layouts.admin')

@section('title', 'View Leave Request')

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Leave Request Details</h3>
                <div class="card-tools">
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Employee Name</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->employee->name }}</dd>

                    <dt class="col-sm-4">Employee Number</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->employee->employee_number }}</dd>

                    <dt class="col-sm-4">Leave Type</dt>
                    <dd class="col-sm-8">{{ ucfirst($leaveRequest->leave_type) }} @if($leaveRequest->leave_type == 'extra')<span class="badge badge-danger ml-2">Extra</span>@endif</dd>

                    <dt class="col-sm-4">Leave Dates</dt>
                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M, Y') }}</dd>

                    <dt class="col-sm-4">Total Days</dt>
                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($leaveRequest->start_date)->diffInDays($leaveRequest->end_date) + 1 }}</dd>

                    <dt class="col-sm-4">Reason Provided</dt>
                    <dd class="col-sm-8">{{ $leaveRequest->reason }}</dd>
                    
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($leaveRequest->status == 'approved') <span class="badge badge-success">Approved</span>
                        @elseif($leaveRequest->status == 'rejected') <span class="badge badge-danger">Rejected</span>
                        @else <span class="badge badge-warning">Pending</span>
                        @endif
                    </dd>

                    @if($leaveRequest->attachment_path)
                    <dt class="col-sm-4">Attachment</dt>
                    <dd class="col-sm-8"><a href="{{ asset('storage/' . $leaveRequest->attachment_path) }}" target="_blank">View Attachment</a></dd>
                    @endif
                </dl>
            </div>
            @if($leaveRequest->status == 'pending')
            <div class="card-footer text-right">
                <form method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
                <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}" style="display:inline;" class="ml-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Employee's Leave Status</h3></div>
            <div class="card-body">
                <strong>Leave Period:</strong> {{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}
                <hr>
                <strong>Current Balance</strong>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">Annual <span><span class="badge badge-primary badge-pill">{{ $employee->leaves_annual_remaining }}</span> / {{ $employee->leaves_annual }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Sick <span><span class="badge badge-primary badge-pill">{{ $employee->leaves_sick_remaining }}</span> / {{ $employee->leaves_sick }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Casual <span><span class="badge badge-primary badge-pill">{{ $employee->leaves_casual_remaining }}</span> / {{ $employee->leaves_casual }}</span></li>
                </ul>
                <strong class="mt-3 d-block">Recent Leave History</strong>
                <table class="table table-sm mt-2">
                    <tbody>
                        @forelse($recentRequests as $recent)
                        <tr>
                            <td>{{ ucfirst($recent->leave_type) }}</td>
                            <td>{{ \Carbon\Carbon::parse($recent->start_date)->format('d M') }}</td>
                            <td>
                                @if($recent->status == 'approved') <span class="badge badge-success">Approved</span>
                                @elseif($recent->status == 'rejected') <span class="badge badge-danger">Rejected</span>
                                @else <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td>No recent history.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection