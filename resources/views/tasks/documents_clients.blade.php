@extends('layouts.admin')

@section('title', 'Select Client')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0 text-dark"><i class="fas fa-users me-2"></i>Select Client for Documents</h4>
        
        {{-- FIX: Changed 'route' to javascript history back to avoid "Route Not Found" error --}}
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Business Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $client->business_name }}</td>
                            <td>{{ $client->name ?? $client->contact_person ?? 'N/A' }}</td>
                            <td>{{ $client->email }}</td>
                            <td class="text-end pe-4">
                                {{-- This route is correct because we defined it in web.php --}}
                                <a href="{{ route('admin.documents.show', $client->id) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-folder-open me-1"></i> View Documents
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection