@extends('layouts.admin')

@section('title', 'My Leave Requests')

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
            <h3 class="card-title">My Leave Balance</h3>
            <div class="card-tools">
                <span class="badge badge-info">Period: {{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Annual</span>
                            <span class="info-box-number">{{ $employee->leaves_annual_remaining ?? $employee->leaves_annual }} / {{ $employee->leaves_annual }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-briefcase-medical"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sick</span>
                            <span class="info-box-number">{{ $employee->leaves_sick_remaining ?? $employee->leaves_sick }} / {{ $employee->leaves_sick }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="far fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Casual</span>
                            <span class="info-box-number">{{ $employee->leaves_casual_remaining ?? $employee->leaves_casual }} / {{ $employee->leaves_casual }}</span>
                        </div>
                    </div>
                </div>
                 <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Other</span>
                            <span class="info-box-number">{{ $employee->leaves_other_remaining ?? $employee->leaves_other }} / {{ $employee->leaves_other }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Request History</h3>
            <div class="card-tools">
                <a href="{{ route('leave-requests.extra-create') }}" class="btn btn-warning float-right ml-2">Request Extra Leave</a>
                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary float-right">Apply for Leave</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $request)
                    <tr>
                        <td>{{ ucfirst($request->leave_type) }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->end_date)->format('d M, Y') }}</td>
                        <td>{{ Str::limit($request->reason, 30) }}</td>
                        <td>
                            @if($request->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($request->status == 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($request->status == 'pending')
                                <a href="{{ route('leave-requests.edit', $request) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form method="POST" action="{{ route('leave-requests.destroy', $request) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to withdraw this application?')">Delete</button>
                                </form>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">You have not made any leave requests yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection