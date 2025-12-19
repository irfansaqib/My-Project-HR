@extends('layouts.client_portal')
@section('header', 'My Dashboard')

@section('content')

{{-- ✅ DISPLAY ANNOUNCEMENTS HERE (Clients will only see 'Visible to Client' ones) --}}
@include('announcements.partials.display')

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-stat p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Active Tasks</h6>
                    <h2 class="mb-0 fw-bold text-dark">{{ $activeTasks }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="fas fa-spinner fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Pending Approval</h6>
                    <h2 class="mb-0 fw-bold text-dark">{{ $pendingTasks }}</h2>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                    <i class="fas fa-clock fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">New Message</h6>
                    <h5 class="mb-0 fw-bold text-success">Contact Support</h5>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                    <i class="fas fa-comments fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold">Recent Activities</h6>
        <a href="{{ route('client.tasks.create') }}" class="btn btn-sm btn-primary">+ New Request</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Task</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTasks as $task)
                <tr>
                    <td class="ps-4 fw-bold">{{ $task->description }}</td>
                    <td>{{ $task->category->name ?? '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $task->status }}</span></td>
                    <td>{{ $task->created_at->format('d M, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">No recent tasks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection