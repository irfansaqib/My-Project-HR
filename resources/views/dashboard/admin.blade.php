@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    
    {{-- ✅ DISPLAY ANNOUNCEMENTS HERE --}}
    @include('announcements.partials.display')
    
    {{-- 1. WELCOME BANNER --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-info bg-white shadow-sm border-left-info">
                <h5 class="text-info">Welcome back, {{ Auth::user()->name }}!</h5>
                <p class="text-muted mb-0">
                    <i class="fas fa-building mr-1"></i> {{ Auth::user()->business->business_name ?? 'My Business' }} | 
                    <span class="badge badge-info">{{ Auth::user()->getRoleNames()->first() ?? 'Admin' }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- 2. KEY METRICS ROW (HR & Payroll) --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ $activeEmployees }}</h3>
                    <p>Active Employees</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('employees.index', ['status' => 'active']) }}" class="small-box-footer">Manage Staff <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3><sup style="font-size: 20px">Rs.</sup>{{ number_format($monthlyPayrollCost / 1000, 1) }}<small>k</small></h3>
                    <p>Est. Monthly Payroll</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <a href="{{ route('salaries.index') }}" class="small-box-footer">Go to Payroll <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Client::where('status', 'active')->count() }}</h3>
                    <p>Active Clients</p>
                </div>
                <div class="icon"><i class="fas fa-briefcase"></i></div>
                <a href="{{ route('clients.index') }}" class="small-box-footer text-dark">View Clients <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger">
                <div class="inner">
                    <h3>{{ $presentCount }}<sup style="font-size: 15px">/{{ $activeEmployees }}</sup></h3>
                    <p>Present Today</p>
                </div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
                <a href="{{ route('attendances.index') }}" class="small-box-footer">Attendance <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- 3. TASK & WORKFLOW OVERVIEW (New Section) --}}
    <h5 class="mt-4 mb-2 text-dark font-weight-bold"><i class="fas fa-tasks mr-2 text-primary"></i> Work & Tasks Overview</h5>
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Tasks</span>
                    <span class="info-box-number">{{ \App\Models\Task::where('status', 'Pending')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-spinner text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">In Progress</span>
                    <span class="info-box-number">{{ \App\Models\Task::where('status', 'In Progress')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-double text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed (This Month)</span>
                    <span class="info-box-number">{{ \App\Models\Task::where('status', 'Completed')->whereMonth('updated_at', now()->month)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Overdue Tasks</span>
                    <span class="info-box-number">{{ \App\Models\Task::whereNotIn('status', ['Completed', 'Closed'])->whereDate('due_date', '<', now())->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN: Attendance & Recent Joinings --}}
        <section class="col-lg-7 connectedSortable">
            
            {{-- Attendance Chart/Status --}}
            <div class="card card-outline card-primary">
                <div class="card-header border-0">
                    <h3 class="card-title">Today's Attendance Snapshot</h3>
                    <div class="card-tools">
                        @if($notMarkedCount > 0)
                            <span class="badge badge-warning">{{ $notMarkedCount }} Not Marked</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between text-center">
                        <div class="w-25 border-right">
                            <h5 class="text-success font-weight-bold mb-0">{{ $presentCount }}</h5>
                            <span class="text-muted small font-weight-bold">PRESENT</span>
                        </div>
                        <div class="w-25 border-right">
                            <h5 class="text-warning font-weight-bold mb-0">{{ $lateCount }}</h5>
                            <span class="text-muted small font-weight-bold">LATE</span>
                        </div>
                        <div class="w-25 border-right">
                            <h5 class="text-info font-weight-bold mb-0">{{ $leaveCount }}</h5>
                            <span class="text-muted small font-weight-bold">ON LEAVE</span>
                        </div>
                        <div class="w-25">
                            <h5 class="text-danger font-weight-bold mb-0">{{ $absentCount }}</h5>
                            <span class="text-muted small font-weight-bold">ABSENT</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Employees --}}
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Recently Joined Staff</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Dept</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentJoinings as $emp)
                            <tr>
                                <td>
                                    <img src="{{ $emp->photo_path ? asset('storage/'.$emp->photo_path) : asset('adminlte/dist/img/default-150x150.png') }}" class="img-circle img-size-32 mr-2">
                                    {{ $emp->name }}
                                </td>
                                <td>{{ $emp->department->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($emp->joining_date)->diffForHumans() }}</td>
                                <td class="text-right"><a href="{{ route('employees.show', $emp->id) }}" class="btn btn-sm btn-light"><i class="fas fa-search"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No recent joinings.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- RIGHT COLUMN: Quick Actions & Alerts --}}
        <section class="col-lg-5 connectedSortable">
            
            {{-- QUICK ACTIONS GRID --}}
            <div class="card bg-light">
                <div class="card-header border-0"><h3 class="card-title font-weight-bold">Quick Launch</h3></div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <a href="{{ route('tasks.create') }}" class="btn btn-white btn-block text-left shadow-sm border-left-primary">
                                <i class="fas fa-plus-circle text-primary mr-2"></i> Create Task
                            </a>
                        </div>
                        <div class="col-6 mb-2">
                            <a href="{{ route('clients.create') }}" class="btn btn-white btn-block text-left shadow-sm border-left-info">
                                <i class="fas fa-user-plus text-info mr-2"></i> Add Client
                            </a>
                        </div>
                        <div class="col-6 mb-2">
                            <a href="{{ route('employees.create') }}" class="btn btn-white btn-block text-left shadow-sm border-left-success">
                                <i class="fas fa-user-tie text-success mr-2"></i> New Employee
                            </a>
                        </div>
                        <div class="col-6 mb-2">
                            <a href="{{ route('salaries.generate.form') }}" class="btn btn-white btn-block text-left shadow-sm border-left-warning">
                                <i class="fas fa-file-invoice-dollar text-warning mr-2"></i> Generate Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if($missingSalaryCount > 0)
            <div class="card card-outline card-danger">
                <div class="card-header"><h3 class="card-title text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Data Alert</h3></div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $missingSalaryCount }}</strong> active employees have 0 Gross Salary defined.</p>
                    <a href="{{ route('employees.index') }}" class="btn btn-xs btn-danger">Fix Salary Data</a>
                </div>
            </div>
            @endif

        </section>
    </div>
</div>
@endsection