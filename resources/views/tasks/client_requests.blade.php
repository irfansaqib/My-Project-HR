@extends('layouts.admin')

@section('title', 'Incoming Client Requests')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0 text-dark"><i class="fas fa-inbox me-2"></i>Incoming Client Requests</h4>
        
        <div class="d-flex align-items-center">
            {{-- 
                NEW DOCUMENTS BUTTON 
                This links to a page where you can select a client to view their documents.
                Ensure you have a route named 'admin.documents.clients' or change this href.
            --}}
            <a href="{{ route('admin.documents.clients') }}" class="btn btn-white border shadow-sm text-primary me-3">
                <i class="fas fa-file-alt me-1"></i> Documents
            </a>

            <ol class="breadcrumb float-sm-right mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Client Requests</li>
            </ol>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">Pending Requests</h3>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="ps-4 text-nowrap">ID</th>
                            <th class="text-nowrap">Client Details</th>
                            <th class="text-nowrap">Subject</th>
                            <th class="text-nowrap">Date Submitted</th>
                            <th class="text-nowrap">Assigned To</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-right pe-4 text-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            {{-- Displays Auto-Generated Task Number (e.g. TSK-2025-001) or ID --}}
                            <td class="ps-4 fw-bold text-nowrap">{{ $task->task_number ?? $task->id }}</td>
                            
                            <td class="text-nowrap">
                                <div class="user-block">
                                    <span class="username text-primary" style="margin-left: 0px;">
                                        {{ $task->client->business_name ?? $task->client->name ?? 'Unknown Client' }}
                                    </span>
                                    <div class="description text-muted small" style="margin-left: 0px;">
                                        {{ $task->creator->email ?? 'No Email' }}
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="fw-bold">{{ Str::limit($task->description ?? 'No Description', 60) }}</span>
                            </td>

                            <td class="text-nowrap">
                                {{ $task->created_at->format('d M, Y') }}<br>
                                <small class="text-muted">{{ $task->created_at->format('h:i A') }}</small>
                            </td>

                            <td class="text-nowrap">
                                @if($task->assignedEmployee)
                                    <span class="badge badge-success">
                                        {{ $task->assignedEmployee->name }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">Unassigned</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @php
                                    $statusClass = match($task->status) {
                                        'Completed' => 'success',
                                        'In Progress' => 'primary',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">{{ $task->status }}</span>
                            </td>

                            <td class="text-right pe-4 text-nowrap">
                                <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View / Assign
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0">No pending client requests found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection