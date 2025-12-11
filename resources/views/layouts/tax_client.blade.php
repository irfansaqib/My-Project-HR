@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">{{ $client->name }}</h1>
            <p class="mb-0 text-muted">
                <span class="mr-3"><i class="fas fa-id-card mr-1"></i> NTN: <strong>{{ $client->ntn ?? 'N/A' }}</strong></span>
                <span>
                    Status: 
                    <span class="badge badge-{{ $client->status === 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </span>
            </p>
        </div>
        <a href="{{ route('tax-services.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to Client List
        </a>
    </div>

    {{-- TABS --}}
    <div class="card shadow mb-4">
        <div class="card-header py-2">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tax-services.clients.employees') ? 'active' : '' }}" 
                       href="{{ route('tax-services.clients.employees', $client->id) }}">
                        <i class="fas fa-users mr-1"></i> Employees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tax-services.clients.components') ? 'active' : '' }}" 
                       href="{{ route('tax-services.clients.components', $client->id) }}">
                        <i class="fas fa-cogs mr-1"></i> Salary Components
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tax-services.clients.salary') ? 'active' : '' }}" 
                       href="{{ route('tax-services.clients.salary', $client->id) }}">
                        <i class="fas fa-calculator mr-1"></i> Salary Input & Sheets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tax-services.clients.reports') ? 'active' : '' }}" 
                       href="{{ route('tax-services.clients.reports', $client->id) }}">
                        <i class="fas fa-chart-bar mr-1"></i> Reports
                    </a>
                </li>
                {{-- NEW TAB ADDED HERE --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tax-services.clients.certificates') ? 'active' : '' }}" 
                       href="{{ route('tax-services.clients.certificates', $client->id) }}">
                        <i class="fas fa-certificate mr-1"></i> Tax Certificates
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            @yield('tab-content')
        </div>
    </div>
</div>
@endsection