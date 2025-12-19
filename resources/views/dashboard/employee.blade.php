@extends('layouts.admin')
@section('title', 'My Portal')

@section('content')
<div class="container-fluid">
    
    {{-- ✅ DISPLAY ANNOUNCEMENTS HERE --}}
    @include('announcements.partials.display')
    
    {{-- 1. WELCOME BANNER --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-success bg-white shadow-sm border-left-success">
                <h5 class="text-success">Welcome back, {{ $employee->name }}!</h5>
                <p class="text-muted mb-0">
                    {{ $employee->designation->title ?? 'Employee' }} | 
                    {{ $employee->department->name ?? 'General' }}
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN: Attendance & Tasks --}}
        <div class="col-md-4">
            
            {{-- ATTENDANCE STATUS --}}
            <div class="card card-primary card-outline mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i> Today's Status</h3>
                </div>
                <div class="card-body text-center">
                    @if($attendanceStats['today_status'])
                        <div class="display-4 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                        <h3 class="text-success font-weight-bold">Present</h3>
                        <div class="mt-3">
                            <span class="badge badge-light border p-2 mr-2">
                                <i class="fas fa-sign-in-alt text-success"></i> In: {{ \Carbon\Carbon::parse($attendanceStats['today_status']->check_in)->format('h:i A') }}
                            </span>
                            @if($attendanceStats['today_status']->check_out)
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-sign-out-alt text-danger"></i> Out: {{ \Carbon\Carbon::parse($attendanceStats['today_status']->check_out)->format('h:i A') }}
                                </span>
                            @else
                                <span class="badge badge-warning p-2">Active Shift</span>
                            @endif
                        </div>
                    @else
                        <div class="display-4 text-secondary mb-2"><i class="fas fa-user-clock"></i></div>
                        <h4 class="text-secondary">Not Marked Yet</h4>
                    @endif
                    
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-6 border-right">
                            <h5 class="text-primary font-weight-bold mb-0">{{ $attendanceStats['present'] }}</h5>
                            <small class="text-muted">Days Present</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger font-weight-bold mb-0">{{ $attendanceStats['late'] }}</h5>
                            <small class="text-muted">Late Arrivals</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MY TASKS WIDGET (NEW) --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks mr-2"></i> Work & Tasks</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Assigned Today
                            <span class="badge badge-primary badge-pill">
                                {{ \App\Models\Task::where('assigned_to', $employee->id)->whereDate('created_at', now())->count() }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Due Today
                            <span class="badge badge-danger badge-pill">
                                {{ \App\Models\Task::where('assigned_to', $employee->id)->whereDate('due_date', now())->whereNotIn('status', ['Completed','Closed'])->count() }}
                            </span>
                        </li>
                        <li class="list-group-item text-center">
                            <a href="{{ route('tasks.my') }}" class="btn btn-sm btn-info btn-block">Go to My Task Board</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Leaves, Pay, Requests --}}
        <div class="col-md-8">
            
            {{-- LEAVE BALANCES --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i> Leave Balances</h3>
                    <div>
                        <a href="{{ route('leave-requests.create') }}" class="btn btn-sm btn-primary shadow-sm">Apply Leave</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($leaveBalances as $balance)
                            <div class="col-sm-4 mb-2">
                                <div class="info-box shadow-none border bg-light mb-0">
                                    <span class="info-box-icon bg-white text-info"><i class="far fa-calendar-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text small font-weight-bold">{{ $balance['name'] }}</span>
                                        <span class="info-box-number">
                                            <span class="text-primary">{{ $balance['remaining'] }}</span> <span class="text-muted small">/ {{ $balance['total'] }}</span>
                                        </span>
                                        <div class="progress progress-xs mt-1">
                                            @php $percent = $balance['total'] > 0 ? ($balance['used'] / $balance['total']) * 100 : 0; @endphp
                                            <div class="progress-bar bg-info" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- LAST PAY --}}
                <div class="col-md-6">
                    <div class="card card-success card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Last Salary</h3>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-between">
                            @if($lastSalary)
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">{{ $lastSalary->salarySheet->month->format('F, Y') }}</h5>
                                    <span class="text-success font-weight-bold h4">PKR {{ number_format($lastSalary->net_salary) }}</span>
                                </div>
                                <a href="{{ route('salaries.payslip', $lastSalary->id) }}" target="_blank" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-download"></i> Slip
                                </a>
                            @else
                                <span class="text-muted">No records found.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- LOAN STATUS --}}
                <div class="col-md-6">
                    <div class="card card-danger card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-2"></i> Loan Status</h3>
                        </div>
                        <div class="card-body text-center">
                            @if($loanBalance > 0)
                                <h4 class="text-danger font-weight-bold mb-0">PKR {{ number_format($loanBalance) }}</h4>
                                <small class="text-muted">Outstanding Amount</small>
                            @else
                                <div class="text-success mt-2">
                                    <i class="fas fa-check-circle fa-lg mb-1"></i><br>No Active Loans
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- RECENT REQUESTS --}}
            <div class="card mt-3">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title">Recent Requests</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRequests as $req)
                                <tr>
                                    <td>{{ $req->leave_type ?? 'Request' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M, Y') }}</td>
                                    <td>
                                        @if($req->status == 'approved') <span class="badge badge-success">Approved</span>
                                        @elseif($req->status == 'pending') <span class="badge badge-warning">Pending</span>
                                        @else <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted small">No recent activity.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection