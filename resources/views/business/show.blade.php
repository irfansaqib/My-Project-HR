@extends('layouts.admin')
@section('title', 'Business Profile')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body pt-0">
                <div class="text-center">
                    <img class="profile-user-img img-fluid" style="width: 300px; height: 300px; object-fit: contain; border: none; padding: 0;" src="{{ $business->logo_path ? asset('storage/' . $business->logo_path) : 'https://via.placeholder.com/300' }}" alt="Business logo">
                </div>
                <h3 class="profile-username text-center">{{ $business->name }}</h3>
                <p class="text-muted text-center">{{ $business->business_type ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Business Details</h3></div>
            <div class="card-body">
                <strong><i class="fas fa-book mr-1"></i> Legal Name</strong>
                <p class="text-muted">{{ $business->legal_name }}</p><hr>
                <strong><i class="fas fa-id-card mr-1"></i> Registration / CNIC No.</strong>
                <p class="text-muted">{{ $business->registration_number ?? 'N/A' }}</p><hr>
                <strong><i class="fas fa-file-alt mr-1"></i> NTN No.</strong>
                <p class="text-muted">{{ $business->ntn_number ?? 'N/A' }}</p><hr>
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">{{ $business->address }}</p><hr>
                <strong><i class="fas fa-phone mr-1"></i> Contact Phone</strong>
                <p class="text-muted">{{ $business->phone_number }}</p><hr>
                <strong><i class="fas fa-envelope mr-1"></i> Contact Email</strong>
                <p class="text-muted">{{ $business->email }}</p>
            </div>
            <div class="card-footer">
                <a href="{{ route('business.edit', $business) }}" class="btn btn-primary">Edit Details</a>
                {{-- THIS BUTTON IS NOW ADDED BACK AND WILL WORK --}}
                <a href="{{ route('business-bank-accounts.index') }}" class="btn btn-info">Manage Bank Accounts</a>
            </div>
        </div>
    </div>
</div>
@endsection