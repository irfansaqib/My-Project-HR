<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    {{-- Main Header Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><i class="far fa-user"></i></a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">{{ Auth::user()->name ?? 'Guest User' }}</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="fas fa-user mr-2"></i> Profile</a>
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
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="fas fa-expand-arrows-alt"></i></a>
            </li>
        </ul>
    </nav>

    {{-- Main Sidebar Container --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ Auth::user()->business && Auth::user()->business->logo_path ? asset('storage/' . Auth::user()->business->logo_path) : asset('adminlte/dist/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->name ?? 'Guest User' }}</a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    
                    @if (Auth::user()?->business)
                        <li class="nav-item"><a href="{{ route('business.show', ['business' => Auth::user()->business->id]) }}" class="nav-link {{ request()->routeIs('business.*') ? 'active' : '' }}"><i class="nav-icon fas fa-briefcase"></i><p>Business Profile</p></a></li>
                    @else
                        @can('business-create')
                        <li class="nav-item"><a href="{{ route('business.create') }}" class="nav-link {{ request()->routeIs('business.create') ? 'active' : '' }}"><i class="nav-icon fas fa-plus-circle"></i><p>Create Business</p></a></li>
                        @endcan
                    @endif
                    
                    <li class="nav-header">ADMINISTRATION</li>
                    @canany(['user-view', 'role-view'])
                    <li class="nav-item {{ request()->routeIs(['users.*', 'roles.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Users & Roles <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('user-view')
                            <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Users</p></a></li>
                            @endcan
                            @can('role-view')
                            <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Roles</p></a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                    
                    {{-- THIS LINK IS NOW CORRECTED --}}
                    @can('client-credential-view')
                     <li class="nav-item">
                        <a href="{{ route('client-credentials.index') }}" class="nav-link {{ request()->routeIs('client-credentials.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Client Credentials</p>
                        </a>
                    </li>
                    @endcan

                    <li class="nav-header">HR MANAGEMENT</li>
                    @canany(['employee-view', 'designation-view', 'department-view'])
                    <li class="nav-item {{ request()->routeIs(['employees.*', 'designations.*', 'departments.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Employees<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('employee-view')
                            <li class="nav-item"><a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Employee List</p></a></li>
                            @endcan
                            @can('designation-view')
                            <li class="nav-item"><a href="{{ route('designations.index') }}" class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Designations</p></a></li>
                            @endcan
                            @can('department-view')
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Departments</p></a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @canany(['salary-component-view', 'salary-sheet-view', 'tax-rate-view'])
                    <li class="nav-item {{ request()->routeIs(['salary-components.*', 'salaries.*', 'tax-rates.*', 'payrolls.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>Payroll<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('salary-component-view')
                            <li class="nav-item"><a href="{{ route('salary-components.index') }}" class="nav-link {{ request()->routeIs('salary-components.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Components</p></a></li>
                            @endcan
                            @can('salary-sheet-view')
                            <li class="nav-item"><a href="{{ route('salaries.index') }}" class="nav-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Salary Sheets</p></a></li>
                            @endcan
                            @can('tax-rate-view')
                            <li class="nav-item"><a href="{{ route('tax-rates.index') }}" class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tax Rates</p></a></li>
                            @endcan
                            @can('payroll-view')
                            <li class="nav-item"><a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Run Payroll</p></a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @canany(['leave-application-view', 'leave-type-view'])
                    <li class="nav-item {{ request()->routeIs(['leave-applications.*', 'leave-types.*']) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Leave Management<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('leave-application-create')
                            <li class="nav-item"><a href="{{ route('leave-applications.create') }}" class="nav-link {{ request()->routeIs('leave-applications.create') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Apply for Leave</p></a></li>
                            @endcan
                            @can('leave-application-view')
                            <li class="nav-item"><a href="{{ route('leave-applications.index') }}" class="nav-link {{ request()->routeIs('leave-applications.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Applications</p></a></li>
                            @endcan
                            @can('leave-type-view')
                            <li class="nav-item"><a href="{{ route('leave-types.index') }}" class="nav-link {{ request()->routeIs('leave-types.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Leave Types</p></a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Main Content & Footer --}}
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
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">Version 1.0</div>
        <strong>Copyright &copy; 2024-2025 <a href="#">Your Company</a>.</strong> All rights reserved.
    </footer>
</div>
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>