@extends('layouts.admin')
@section('title', 'Business Profile')

@section('content')
<div class="row">
    <!-- Left Column: Logo + Name -->
    <div class="col-md-4">
        <div class="card card-primary card-outline h-100">
            <div class="card-body box-profile d-flex flex-column justify-content-center align-items-center">
                {{-- ✅ Centered logo inside the card --}}
                <div class="d-flex justify-content-center align-items-center" style="height: 180px;">
                    <img class="img-fluid"
                         style="max-width: 280px; max-height: 280px; object-fit: contain;"
                         src="{{ $business->logo_path ? asset('storage/' . $business->logo_path) : asset('adminlte/dist/img/AdminLTELogo.png') }}"
                         alt="Business logo">
                </div>
                <h3 class="profile-username text-center mt-3">{{ $business->business_name }}</h3>
                <p class="text-muted text-center">{{ $business->business_type ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Business Details -->
    <div class="col-md-8">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header">
                <h3 class="card-title mb-0">Business Details</h3>
            </div>
            <div class="card-body flex-grow-1">
                <strong><i class="fas fa-book mr-1"></i> Legal Name</strong>
                <p class="text-muted">{{ $business->legal_name ?? 'N/A' }}</p>
                <hr>
                <strong><i class="fas fa-id-card mr-1"></i> Registration No.</strong>
                <p class="text-muted">{{ $business->registration_number ?? 'N/A' }}</p>
                <hr>
                <strong><i class="fas fa-file-alt mr-1"></i> NTN No.</strong>
                <p class="text-muted">{{ $business->ntn_number ?? 'N/A' }}</p>
                <hr>
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">{{ $business->address ?? 'N/A' }}</p>
                <hr>
                <strong><i class="fas fa-phone mr-1"></i> Contact Phone</strong>
                <p class="text-muted">{{ $business->phone_number ?? 'N/A' }}</p>
                <hr>
                <strong><i class="fas fa-envelope mr-1"></i> Contact Email</strong>
                <p class="text-muted">{{ $business->email ?? 'N/A' }}</p>
            </div>
            <div class="card-footer d-flex justify-content-start">
                <a href="{{ route('business.edit', $business) }}" class="btn btn-primary mr-2">
                    Edit Details
                </a>
                <a href="{{ route('business-bank-accounts.index') }}" class="btn btn-info">
                    Manage Bank Accounts
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
