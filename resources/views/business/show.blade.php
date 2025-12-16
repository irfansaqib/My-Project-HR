@extends('layouts.admin')

@section('title', 'Business Profile')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body pt-3 pb-3 text-center">
                @if(!empty($business->logo_path) && file_exists(public_path('storage/' . $business->logo_path)))
                    <img class="profile-user-img img-fluid"
                         src="{{ asset('storage/' . $business->logo_path) }}"
                         alt="Business Logo"
                         style="width: 280px; height: 280px; object-fit: contain; border: 1px solid #e0e0e0; padding: 8px; border-radius: 10px;">
                @else
                    <div class="d-flex align-items-center justify-content-center"
                         style="width: 280px; height: 280px; background: #f8f9fa; border: 1px dashed #ccc; border-radius: 10px;">
                        <span class="text-muted">No Logo Uploaded</span>
                    </div>
                @endif

                <h3 class="profile-username text-center mt-3 mb-0">
                    {{ $business->business_name }}
                </h3>
                <p class="text-muted text-center">
                    {{ $business->business_type ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i>Business Details</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-book mr-1"></i> Legal Name</strong>
                <p class="text-muted">{{ $business->legal_name }}</p>
                <hr>

                <strong><i class="fas fa-id-card mr-1"></i> Registration / CNIC No.</strong>
                <p class="text-muted">{{ $business->registration_number ?? 'N/A' }}</p>
                <hr>

                <strong><i class="fas fa-file-alt mr-1"></i> NTN No.</strong>
                <p class="text-muted">{{ $business->ntn_number ?? 'N/A' }}</p>
                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">{{ $business->address ?: 'N/A' }}</p>
                <hr>

                <strong><i class="fas fa-phone mr-1"></i> Contact Phone</strong>
                <p class="text-muted">{{ $business->phone_number ?: 'N/A' }}</p>
                <hr>

                <strong><i class="fas fa-envelope mr-1"></i> Contact Email</strong>
                <p class="text-muted">{{ $business->email ?: 'N/A' }}</p>

                <hr>
                
                <strong><i class="fas fa-globe mr-1"></i> Client Portal Access</strong>
                <div class="mt-2">
                    @if($business->portal_code)
                        <div class="alert alert-light border d-flex justify-content-between align-items-center" style="background-color: #f8f9fa;">
                            <div>
                                <span class="text-muted small d-block">Portal Code:</span>
                                <span class="h5 mb-0 text-primary font-weight-bold" id="portalCodeText">{{ $business->portal_code }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyPortalCode()">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning p-2 small">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Not Assigned
                        </div>
                        <form action="{{ route('business.generate-code') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-magic mr-1"></i> Generate Portal Code
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <div>
                    <a href="{{ route('business.edit', $business) }}" class="btn btn-primary">
                        <i class="fas fa-edit mr-1"></i> Edit Details
                    </a>
                    <a href="{{ route('business-bank-accounts.index') }}" class="btn btn-info">
                        <i class="fas fa-university mr-1"></i> Manage Bank Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyPortalCode() {
        var range = document.createRange();
        range.selectNode(document.getElementById("portalCodeText"));
        window.getSelection().removeAllRanges(); 
        window.getSelection().addRange(range); 
        document.execCommand("copy");
        window.getSelection().removeAllRanges();
        alert("Portal Code copied to clipboard!");
    }
</script>
@endsection
