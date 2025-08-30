<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
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
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ asset('adminlte/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name ?? 'Guest User' }}</a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    
                    {{-- ============================================================= --}}
                    {{-- === THIS IS THE CORRECTED LOGIC TO FIX THE ERROR          === --}}
                    {{-- ============================================================= --}}
                    @if (Auth::user() && Auth::user()->business)
                        {{-- If user HAS a business, link directly to their profile page --}}
                        <li class="nav-item"><a href="{{ route('business.show', Auth::user()->business) }}" class="nav-link"><i class="nav-icon fas fa-briefcase"></i><p>Business Profile</p></a></li>
                    @else
                        {{-- If user does NOT have a business, link to the create page --}}
                        <li class="nav-item"><a href="{{ route('business.create') }}" class="nav-link"><i class="nav-icon fas fa-plus-circle"></i><p>Create Business</p></a></li>
                    @endif
                    {{-- ============================================================= --}}
                    
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Users</p></a></li>
                    <li class="nav-item"><a href="{{ route('client-credentials.index') }}" class="nav-link"><i class="nav-icon fas fa-key"></i><p>Client Credentials</p></a></li>
                    <li class="nav-item"><a href="{{ route('customers.index') }}" class="nav-link"><i class="nav-icon fas fa-address-book"></i><p>Customers</p></a></li>

                    <li class="nav-header">HR MANAGEMENT</li>
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Employees<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('employees.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Employee List</p></a></li>
                            <li class="nav-item"><a href="{{ route('designations.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Designations</p></a></li>
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Departments</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Leaves<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('leave-requests.create') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Apply for Leave</p></a></li>
                            <li class="nav-item"><a href="{{ route('leave-requests.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>My Leaves & Approvals</p></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content">
            <div class="container-fluid pt-3">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">Anything you want</div>
        <strong>Copyright &copy; 2024-2025 <a href="#">Your Company</a>.</strong> All rights reserved.
    </footer>
</div>

<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>