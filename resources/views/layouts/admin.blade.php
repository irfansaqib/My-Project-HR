<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @vite('resources/css/app.css')
    <!-- FullCalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <!-- Custom Styles for Modern Look -->
    <style>
        :root {
            --primary-color: #4A90E2;
            --primary-hover-color: #357ABD;
            --topbar-bg: #ffffff;
            --sidebar-bg: #1f2d3d;
            --sidebar-text-color: rgba(255, 255, 255, 0.7);
            --sidebar-hover-bg: #2c3e50;
            --sidebar-active-bg: var(--primary-color);
            --sidebar-active-text: #ffffff;
            --content-bg: #f4f6f9;
            --border-color: #e5e7eb;
            --topbar-text-color: #333;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        /* ===== Main Layout & Wrapper ===== */
        .content-wrapper {
            background-color: var(--content-bg);
        }

        /* ===== Top Navigation Bar ===== */
        .main-header {
            background-color: var(--topbar-bg);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .main-header .nav-link,
        .main-header a.nav-link {
            color: var(--topbar-text-color);
        }
        .user-dropdown-image {
            width: 28px;
            height: 28px;
        }

        /* ===== Main Sidebar Container ===== */
        .main-sidebar {
            background-color: var(--sidebar-bg);
            border-right: none;
            box-shadow: none;
        }

        /* ===== Brand Logo ===== */
        .brand-link {
            border-bottom: 1px solid #2c3e50;
            padding: 0 !important;
        }
        .logo-container {
            background-color: #ffffff;
            padding: 0.75rem;
            text-align: center;
        }
        .logo-container img {
            max-height: 40px;
            width: auto;
        }
        
        /* ===== Sidebar Navigation ===== */
        .sidebar {
            padding: 8px;
        }
        
        .nav-sidebar .nav-item .nav-link {
            color: var(--sidebar-text-color);
            transition: background-color 0.2s ease, color 0.2s ease;
            border-radius: 4px;
            margin: 2px 0;
            font-weight: 500;
        }

        .nav-sidebar .nav-item .nav-link:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--sidebar-active-text);
        }
        
        /* --- Active Link Styling --- */
        .nav-sidebar .nav-item > .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 600;
            box-shadow: none;
        }
         .nav-sidebar .nav-item > .nav-link.active i {
            color: var(--sidebar-active-text);
        }

        /* --- Treeview (Dropdown Menu) Styling --- */
        .nav-item.has-treeview.menu-open > .nav-link {
            background-color: var(--sidebar-hover-bg);
            color: var(--sidebar-active-text);
        }
        
        .nav-treeview {
            background-color: rgba(0,0,0,0.15);
            padding-left: 1rem;
            margin-top: 4px;
            border-radius: 4px;
        }

        .nav-treeview .nav-link {
            font-size: 0.9rem;
            color: var(--sidebar-text-color);
        }

        .nav-treeview .nav-link.active,
        .nav-treeview .nav-link:hover {
            background-color: transparent;
            color: var(--sidebar-active-text);
            font-weight: 500;
        }
        .nav-treeview .nav-link.active i.nav-icon {
            color: var(--sidebar-active-text);
        }

        /* ===== Content Area ===== */
        .content-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
        }

        /* Enhance card styles for a modern feel */
        .card {
            border: 1px solid #ddd;
            border-top: 3px solid var(--primary-color);
            border-radius: 0.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.07);
        }
        
        .card-header {
             background-color: #fff;
             border-bottom: 1px solid #f4f4f4;
             font-weight: 600;
             color: #444;
        }

    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link" style="font-weight: 600; font-size: 1.1rem;">{{ Auth::user()->business->business_name ?? config('app.name', 'Laravel') }}</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                    <img src="{{ asset('adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle mr-2 user-dropdown-image" alt="User Image">
                    <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Guest User' }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">{{ Auth::user()->name ?? 'Guest User' }}</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item dropdown-footer"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <div class="logo-container">
                <img src="{{ Auth::user()->business && Auth::user()->business->logo_path ? asset('storage/' . Auth::user()->business->logo_path) : asset('adminlte/dist/img/AdminLTELogo.png') }}" alt="Logo">
            </div>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Menu items are correct from previous step -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>DASHBOARD</p>
                        </a>
                    </li>
                    @if(Auth::user()->business)
                    <li class="nav-item">
                        <a href="{{ route('business.show', Auth::user()->business->id) }}" class="nav-link {{ request()->routeIs('business.show') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>BUSINESS PROFILE</p>
                        </a>
                    </li>
                    @else
                    <li class="nav-item">
                        <a href="{{ route('business.create') }}" class="nav-link {{ request()->routeIs('business.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-plus-circle"></i>
                            <p>Create Business Profile</p>
                        </a>
                    </li>
                    @endif
                    @hasanyrole('Owner|Admin')
                    <li class="nav-item has-treeview {{ request()->routeIs('users.*', 'roles.*', 'email-configuration.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>ADMINISTRATION <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                 <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Users</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Roles</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('email-configuration.edit') }}" class="nav-link {{ request()->routeIs('email-configuration.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Email Settings</p></a>
                            </li>
                        </ul>
                    </li>
                    @endhasanyrole
                    <li class="nav-item">
                        <a href="{{ route('client-credentials.index') }}" class="nav-link {{ request()->routeIs('client-credentials.*') ? 'active' : '' }}"><i class="nav-icon fas fa-key"></i><p>CLIENT CREDENTIALS</p></a>
                    </li>
                    <li class="nav-item has-treeview {{ request()->routeIs(['employees.*', 'designations.*', 'departments.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link"><i class="nav-icon fas fa-users"></i><p>HR MANAGEMENT <i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Employees List</p></a></li>
                            <li class="nav-item"><a href="{{ route('designations.index') }}" class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Designations</p></a></li>
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Departments</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item has-treeview {{ request()->routeIs(['attendances.*', 'shifts.*', 'shift-assignments.*', 'holidays.*', 'leave-types.*', 'leave-requests.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Leave & Attendance <i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                             <li class="nav-item"><a href="{{ route('attendances.index') }}" class="nav-link {{ request()->routeIs('attendances.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Daily Attendance</p></a></li>
                             <li class="nav-item"><a href="{{ route('attendances.bulk.create') }}" class="nav-link {{ request()->routeIs('attendances.bulk.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Bulk Mark Attendance</p></a></li>
                             <li class="nav-item"><a href="{{ route('shifts.index') }}" class="nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Work Shifts</p></a></li>
                             <li class="nav-item"><a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Applications</p></a></li>
                             <li class="nav-item"><a href="{{ route('leave-types.index') }}" class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Types</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item has-treeview {{ request()->routeIs(['salary-components.*', 'salaries.*', 'tax-rates.*', 'payrolls.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link"><i class="nav-icon fas fa-file-invoice"></i><p>Payroll <i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                             <li class="nav-item"><a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Run Payroll</p></a></li>
                             <li class="nav-item"><a href="{{ route('salary-components.index') }}" class="nav-link {{ request()->routeIs('salary-components.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Components</p></a></li>
                            <li class="nav-item"><a href="{{ route('salaries.index') }}" class="nav-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Sheets</p></a></li>
                            <li class="nav-item"><a href="{{ route('tax-rates.index') }}" class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tax Rates</p></a></li>
                             <li class="nav-item"><a href="{{ route('payrolls.history') }}" class="nav-link {{ request()->routeIs('payrolls.history') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Payroll History</p></a></li>
                        </ul>
                    </li>
                     <li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Reports <i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Attendance Report</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.attendance-calendar') }}" class="nav-link {{ request()->routeIs('reports.attendance-calendar') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Attendance Calendar</p></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>@yield('title')</h1></div></div></div>
        </section>
        <div class="content">
            <div class="container-fluid">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @yield('content')
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">Version 1.0</div>
        <strong>Copyright &copy; 2024-2025 <a href="#">Your Company</a>.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- Scripts -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>

