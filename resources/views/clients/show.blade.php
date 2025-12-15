@extends('layouts.admin')
@section('title', $client->business_name)

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Client Profile</h1>
        <div>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
            </a>
            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit Client
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Client Details --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Business Details</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="sidebar-brand-icon rotate-n-15 d-inline-block">
                            <i class="fas fa-building fa-4x text-gray-300"></i>
                        </div>
                        <h4 class="mt-3 font-weight-bold text-primary">{{ $client->business_name }}</h4>
                        <p class="text-muted mb-1">{{ $client->contact_person }}</p>
                        <span class="badge badge-{{ $client->status == 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($client->status) }}
                        </span>
                    </div>
                    
                    <hr>

                    <div class="mb-3">
                        <strong class="small text-uppercase text-muted">Email Address</strong><br>
                        <span><a href="mailto:{{ $client->email }}">{{ $client->email }}</a></span>
                    </div>

                    <div class="mb-3">
                        <strong class="small text-uppercase text-muted">NTN / CNIC</strong><br>
                        <span>{{ $client->ntn_cnic }}</span>
                    </div>

                    @if($client->phone)
                    <div class="mb-3">
                        <strong class="small text-uppercase text-muted">Phone</strong><br>
                        <span>{{ $client->phone }}</span>
                    </div>
                    @endif

                    @if($client->address)
                    <div class="mb-3">
                        <strong class="small text-uppercase text-muted">Address</strong><br>
                        <span>{{ $client->address }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Assignments & Stats --}}
        <div class="col-lg-8">
            {{-- Assigned Team Card --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assigned Team Members</h6>
                </div>
                <div class="card-body">
                    @if($client->assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Service Type</th>
                                        <th>Assigned Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->assignments as $assign)
                                    <tr>
                                        <td>
                                            <i class="fas fa-user-circle text-gray-400 mr-2"></i>
                                            {{ $assign->employee->name ?? 'Unknown' }}
                                        </td>
                                        <td><span class="badge badge-info">{{ $assign->service_type }}</span></td>
                                        <td>{{ $assign->created_at->format('d M, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-2"></i>
                            <p>No team members assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity Placeholder --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-tasks fa-2x mb-2 text-gray-300"></i>
                    <p class="mb-0">Task history and activity logs will appear here.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection