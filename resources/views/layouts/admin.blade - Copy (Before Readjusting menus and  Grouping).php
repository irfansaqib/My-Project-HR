<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HRMS') }} | @yield('title')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @vite('resources/css/app.css')
    @stack('styles')

    <style>
        /* Custom Brand Logo Area - Kept the Professional Look */
        .brand-link {
            background-color: #ffffff !important;
            color: #333 !important;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
            padding: 10px 15px;
        }
        .brand-link .brand-image {
            float: none;
            line-height: .8;
            margin-left: 0;
            margin-right: 0;
            max-height: 45px; /* Prominent Logo */
            width: auto;
        }
        /* Sidebar Header Styling */
        .nav-header {
            font-size: 0.85rem;
            font-weight: bold;
            color: #aab0b6;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 1rem 0.5rem;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link font-weight-bold text-dark">{{ Auth::user()->business->name ?? 'HR Management System' }}</span>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle mr-1"></i>
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down ml-1 small text-muted"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow">
                    <span class="dropdown-header bg-light">Account</span>
                    <div class="dropdown-divider"></div>
                    
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2 text-primary"></i> My Profile
                    </a>

                    <div class="dropdown-divider"></div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="dropdown-item dropdown-footer text-danger"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            @if(Auth::user()->business && Auth::user()->business->logo_path)
                <img src="{{ asset('storage/' . Auth::user()->business->logo_path) }}" alt="Logo" class="brand-image">
            @else
                <span class="brand-text font-weight-bold text-dark">HRMS</span>
            @endif
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    {{-- ADMINISTRATION (Includes Business Profile) --}}
                    @hasanyrole('Owner|Admin')
                    <li class="nav-item has-treeview {{ request()->routeIs(['business.*', 'users.*', 'roles.*', 'email-configuration.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Administration<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- ✅ Business Profile Moved Here --}}
                            <li class="nav-item">
                                <a href="{{ route('business.show', Auth::user()->business_id) }}" class="nav-link {{ request()->routeIs('business.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i><p>Business Profile</p>
                                </a>
                            </li>
                            <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Users</p></a></li>
                            <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Roles</p></a></li>
                            <li class="nav-item"><a href="{{ route('email-configuration.edit') }}" class="nav-link {{ request()->routeIs('email-configuration.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Email Settings</p></a></li>
                        </ul>
                    </li>
                    @endhasanyrole

                    {{-- HR Management --}}
                    <li class="nav-item has-treeview {{ request()->routeIs(['employees.*', 'designations.*', 'departments.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>HR Management<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Employees List</p></a></li>
                            <li class="nav-item"><a href="{{ route('designations.index') }}" class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Designations</p></a></li>
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Departments</p></a></li>
                        </ul>
                    </li>

                    {{-- EMPLOYEE PORTAL --}}
                    @if(Auth::user()->employee)
                    <li class="nav-item has-treeview {{ request()->routeIs(['leave-requests.*', 'leave-encashments.*', 'salaries.my-history', 'attendances.my', 'salaries.my-tax']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-circle"></i>
                            <p>My Portal<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-header small text-muted ml-3 mt-2">LEAVES</li>
                            <li class="nav-item"><a href="{{ route('leave-requests.create') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Apply Leave</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-requests.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>My Applications</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-requests.extra-create') }}" class="nav-link"><i class="far fa-circle nav-icon text-warning"></i><p>Request Extra Leave</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-encashments.create') }}" class="nav-link"><i class="far fa-circle nav-icon text-info"></i><p>Request Encashment</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-encashments.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Encashment History</p></a></li>

                            <li class="nav-header small text-muted ml-3 mt-2">FINANCE & WORK</li>
                            <li class="nav-item"><a href="{{ route('attendances.my') }}" class="nav-link"><i class="fas fa-calendar-check nav-icon text-primary"></i><p>My Attendance</p></a></li>
                            <li class="nav-item"><a href="{{ route('salaries.my-history') }}" class="nav-link"><i class="fas fa-file-invoice-dollar nav-icon text-success"></i><p>My Payslips</p></a></li>
                            <li class="nav-item"><a href="{{ route('salaries.my-tax') }}" class="nav-link"><i class="fas fa-file-alt nav-icon text-warning"></i><p>Tax Certificate</p></a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('tasks.my') }}" class="nav-link {{ request()->routeIs('tasks.my') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list nav-icon text-info"></i>
                            <p>My Tasks</p>
                        </a>
                    </li>
                    @endif

                    {{-- LEAVE MANAGEMENT (Admin) --}}
                    @hasanyrole('Owner|Admin')
                    <li class="nav-item has-treeview {{ request()->routeIs(['attendances.*', 'shifts.*', 'shift-assignments.*', 'holidays.*', 'leave-types.*', 'leave-requests.index', 'leave-encashments.index']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Leave Management<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('attendances.index') }}" class="nav-link {{ request()->routeIs('attendances.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Attendance</p></a></li>
                            <li class="nav-item"><a href="{{ route('attendances.bulk.create') }}" class="nav-link {{ request()->routeIs('attendances.bulk.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Bulk Attendance</p></a></li>
                            <li class="nav-item"><a href="{{ route('shifts.index') }}" class="nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Work Shifts</p></a></li>
                            <li class="nav-item"><a href="{{ route('shift-assignments.create') }}" class="nav-link {{ request()->routeIs('shift-assignments.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Shift Assignments</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.index') && Auth::user()->hasAnyRole('Owner', 'Admin') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Requests</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-encashments.index') }}" class="nav-link {{ request()->routeIs('leave-encashments.index') ? 'active' : '' }}"><i class="fas fa-coins nav-icon"></i><p>Encashment Requests</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-types.index') }}" class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Types</p></a></li>
                            <li class="nav-item"><a href="{{ route('holidays.index') }}" class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Holidays</p></a></li>
                        </ul>
                    </li>

                    {{-- PAYROLL (Admin) --}}
                    <li class="nav-item has-treeview {{ request()->routeIs(['salary-components.*', 'salaries.*', 'tax-rates.*', 'payrolls.*', 'approvals.salary.*', 'loans.*', 'funds.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Payroll<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('salary-components.index') }}" class="nav-link {{ request()->routeIs('salary-components.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Components</p></a></li>
                            <li class="nav-item"><a href="{{ route('salaries.index') }}" class="nav-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Sheets</p></a></li>
                            <li class="nav-item"><a href="{{ route('tax-rates.index') }}" class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tax Rates</p></a></li>
                            <li class="nav-item"><a href="{{ route('payrolls.history') }}" class="nav-link {{ request()->routeIs('payrolls.history') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Payroll History</p></a></li>
                            <li class="nav-item"><a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}"><i class="fas fa-hand-holding-usd nav-icon"></i><p>Loans & Advances</p></a></li>
                            <li class="nav-item"><a href="{{ route('funds.index') }}" class="nav-link {{ request()->routeIs('funds.index') ? 'active' : '' }}"><i class="fas fa-piggy-bank nav-icon"></i><p>Contributory Funds</p></a></li>
                            <li class="nav-item"><a href="{{ route('funds.transactions.index') }}" class="nav-link {{ request()->routeIs('funds.transactions.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Funds Ledger</p></a></li>
                            <li class="nav-item"><a href="{{ route('approvals.salary.index') }}" class="nav-link {{ request()->routeIs('approvals.salary.*') ? 'active' : '' }}"><i class="far fa-check-circle nav-icon text-info"></i><p>Approvals</p></a></li>
                        </ul>
                    </li>
                    
                    {{-- 1. CLIENT MANAGEMENT MODULE --}}
                    <li class="nav-item">
                        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                Client Management  
                            </p>
                        </a>
                    </li>

                    {{-- 2. Internal Task Manager --}}
                    <li class="nav-item">
                        <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>
                                Task Board
                                {{-- Optional: Badge for pending tasks --}}
                                @php $pendingCount = \App\Models\Task::where('status', 'Pending')->count(); @endphp
                                @if($pendingCount > 0)
                                    <span class="badge badge-warning right">{{ $pendingCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                    {{-- 3. RECURRING TASKS --}}
                    <li class="nav-item">
                        <a href="{{ route('recurring-tasks.index') }}" class="nav-link {{ request()->routeIs('recurring-tasks.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sync-alt"></i>
                            <p>Recurring Rules</p>
                        </a>
                    </li>
                    {{-- 4. ✅ NEW: TASK ANALYTICS REPORT --}}
                    <li class="nav-item">
                        <a href="{{ route('tasks.report') }}" class="nav-link {{ request()->routeIs('tasks.report') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Task Analytics</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('task-categories.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Tasks Categories</p>
                        </a>
                    </li>

                    {{-- ✅ NEW SECTION: TOOLS & UTILITIES --}}
                    <li class="nav-header">TOOLS & UTILITIES</li>
                    <li class="nav-item">
                        <a href="{{ route('tools.taxCalculator') }}" class="nav-link {{ request()->routeIs('tools.taxCalculator') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calculator"></i>
                            <p>Simple Tax Calculator</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tools.bulk-tax') }}" class="nav-link {{ request()->routeIs('tools.bulk-tax*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-csv"></i>
                            <p>Bulk Tax Calculator</p>
                        </a>
                    </li>

                    {{-- REPORTS --}}
                    <li class="nav-header">REPORTS</li>
                    <li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>System Reports<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('reports.payroll') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Payroll Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.attendance') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Attendance Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.leave') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.loans') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Loan Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.funds') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Funds Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.tax') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tax Report</p></a></li>
                        </ul>
                    </li>

                    {{-- OUTSOURCED SERVICES --}}
                    <li class="nav-header">OUTSOURCED SERVICES</li>
                    <li class="nav-item">
                        <a href="{{ route('tax-services.index') }}" class="nav-link {{ request()->routeIs('tax-services.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>Tax Client Services</p>
                        </a>
                    </li>

                    {{-- CLIENT CREDENTIALS --}}
                    <li class="nav-item has-treeview {{ request()->routeIs('client-credentials.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Client Credentials<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('client-credentials.index') }}" class="nav-link {{ request()->routeIs('client-credentials.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>All Credentials</p></a></li>
                            <li class="nav-item"><a href="{{ route('client-credentials.create') }}" class="nav-link {{ request()->routeIs('client-credentials.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Add New</p></a></li>
                        </ul>
                    </li>
                    @endhasanyrole

                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('title')</h1>
                    </div>
                </div>
            </div>
        </section>
        <div class="content">
            <div class="container-fluid">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer text-sm">
        <div class="float-right d-none d-sm-inline">Version 2.1</div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ Auth::user()->business->name ?? 'HR System' }}</a>.</strong> All rights reserved.
    </footer>

</div>

<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>