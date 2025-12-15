@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-primary"><i class="fas fa-inbox me-2"></i>Incoming Client Requests</h4>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">Request ID</th>
                            <th>Client</th>
                            <th>Subject</th>
                            <th>Date Submitted</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td class="ps-4 fw-bold">#{{ $task->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:35px; height:35px">
                                        {{ substr($task->client->name ?? 'C', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $task->client->name ?? 'Unknown Client' }}</div>
                                        <div class="small text-muted">{{ $task->creator->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ Str::limit($task->title, 40) }}</td>
                            <td>{{ $task->created_at->format('d M, Y h:i A') }}</td>
                            <td>
                                @if($task->assignedEmployee)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ $task->assignedEmployee->name }}
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $task->status }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user-plus me-1"></i> Assign / Process
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-light"></i>
                                <p>No pending client requests found.</p>
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