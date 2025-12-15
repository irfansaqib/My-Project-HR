@extends('layouts.client_portal')

@section('header', 'My Tasks')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-list me-2"></i> All Task Requests</h6>
        <a href="{{ route('client.tasks.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Request
        </a>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $task->task_number ?? '#' . $task->id }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $task->category->name ?? 'General' }}
                            </span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;" title="{{ $task->description }}">
                                {{ Str::limit($task->description, 50) }}
                            </div>
                        </td>
                        <td>
                            @php
                                $priorityClass = match($task->priority) {
                                    'Very Urgent' => 'danger',
                                    'Urgent' => 'warning',
                                    default => 'success',
                                };
                            @endphp
                            <span class="badge bg-{{ $priorityClass }} bg-opacity-10 text-{{ $priorityClass }}">
                                {{ $task->priority }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($task->status) {
                                    'Completed' => 'success',
                                    'In Progress' => 'primary',
                                    'Pending' => 'secondary',
                                    default => 'dark',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $task->status }}</span>
                        </td>
                        <td>
                            @if($task->due_date)
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                            @else
                                <span class="text-muted small">Not set</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('client.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p>No tasks found.</p>
                                <a href="{{ route('client.tasks.create') }}" class="btn btn-sm btn-outline-primary mt-2">Create First Task</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($tasks->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $tasks->links() }}
    </div>
    @endif
</div>
@endsection