<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        /* Fixed Sidebar Links */
        .sidebar a { 
            color: rgba(255,255,255,0.8); 
            text-decoration: none; 
            padding: 12px 20px; 
            display: block; 
            border-left: 3px solid transparent; 
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { 
            background-color: #34495e; 
            border-left-color: #3498db; 
            color: white; 
        }
        .topbar { background-color: white; border-bottom: 1px solid #dee2e6; padding: 10px 25px; }
        .card-stat { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        /* Logo Styling */
        .portal-logo {
            max-width: 150px; /* Adjust size as needed */
            height: auto;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

@php
    // Attempt to find the client record associated with the current logged-in User ID
    $clientInfo = \App\Models\Client::where('user_id', Auth::id())->first();
@endphp

<div class="d-flex">
    <div class="sidebar col-md-2 d-none d-md-block">
        <div class="py-4 text-center bg-white">
            <img src="{{ asset('storage/INH_HR_LOGO.png') }}" alt="Portal Logo" class="portal-logo">
            
            <h4 class="fw-bold mb-0 text-dark">CLIENT<span class="text-info">PORTAL</span></h4>
        </div>
        <hr class="border-secondary mx-3">
        <nav class="mt-3">
            <a href="{{ route('client.dashboard') }}" class="{{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            
            <a href="{{ route('client.tasks.create') }}" class="{{ request()->routeIs('client.tasks.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle me-2"></i> New Request
            </a>
            
            <a href="{{ route('client.tasks.index') }}" class="{{ request()->routeIs('client.tasks.index') ? 'active' : '' }}">
                <i class="fas fa-tasks me-2"></i> My Tasks
            </a>

            <a href="{{ route('client.documents.index') }}" class="{{ request()->routeIs('client.documents.index') ? 'active' : '' }}">
                <i class="fas fa-file-alt me-2"></i> Documents
            </a>
            
            <a href="{{ route('client.messages.index') }}" class="{{ request()->routeIs('client.messages.index') ? 'active' : '' }}">
                <i class="fas fa-comments me-2"></i> Messages
            </a>
        </nav>
    </div>

    <div class="flex-grow-1">
        <div class="topbar d-flex justify-content-between align-items-center">
            
            <div class="d-flex align-items-center">
                <h5 class="m-0 text-muted">@yield('header', 'Dashboard')</h5>

                @if($clientInfo)
                    <div class="ms-3 ps-3 border-start border-2">
                        <span class="fw-bold text-danger">
                            {{ $clientInfo->business_name }} 
                        </span>
                        <span class="text-muted mx-1">|</span>
                        <span class="text-dark">
                            {{ $clientInfo->cnic ?? $clientInfo->registration_number ?? 'N/A' }}
                        </span>
                    </div>
                @endif
            </div>
            
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name ?? 'Client' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>