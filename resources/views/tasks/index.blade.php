@extends('layouts.admin')
@section('title', 'Tasks Management')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-tasks mr-2"></i> Tasks List</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus mr-1"></i> Add New Task
        </a>
    </div>
    
    {{-- SEARCH --}}
    <div class="p-3 border-bottom bg-light">
        <form action="{{ route('tasks.index') }}" method="GET" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search Task #, Client..." value="{{ request('search') }}" style="width: 250px;">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0 table-sm text-nowrap">
            <thead class="thead-dark">
                <tr>
                    <th>Task #</th>
                    <th>Client</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Dates</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned By</th>
                    <th class="text-right" style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr>
                    <td class="font-weight-bold text-primary">{{ $task->task_number }}</td>
                    <td class="font-weight-bold">{{ $task->client->business_name }}</td>
                    <td>
                        <small class="d-block text-muted">{{ $task->category->parent->parent->name ?? '' }} > {{ $task->category->parent->name ?? '' }}</small>
                        <strong class="text-dark">{{ $task->category->name ?? '-' }}</strong>
                    </td>
                    <td><span class="d-inline-block text-truncate" style="max-width: 150px;">{{ $task->description }}</span></td>
                    <td style="font-size: 11px;">
                        <div class="text-success"><i class="fas fa-play-circle"></i> {{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d-M-y') : '-' }}</div>
                        <div class="text-danger"><i class="fas fa-stop-circle"></i> {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-M-y') : 'N/A' }}</div>
                    </td>
                    <td>
                        @if($task->assignedEmployee)
                            <span class="badge badge-info">{{ $task->assignedEmployee->name }}</span>
                        @else
                            <span class="badge badge-secondary">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @php $pColor = match($task->priority) { 'Very Urgent'=>'danger', 'Urgent'=>'warning', default=>'success' }; @endphp
                        <span class="badge badge-{{ $pColor }}">{{ $task->priority }}</span>
                    </td>
                    
                    {{-- ✅ UPDATED STATUS COLUMN WITH OVERDUE TAG --}}
                    <td style="vertical-align: middle;">
                        <span class="badge badge-light border d-block mb-1">{{ $task->status }}</span>
                        
                        @if($task->isOverdue())
                            <span class="badge badge-danger text-uppercase" style="font-size: 9px; letter-spacing: 0.5px; display: block;">
                                <i class="fas fa-exclamation-circle mr-1"></i> Overdue
                            </span>
                        @endif
                    </td>

                    <td class="small">{{ $task->creator->name ?? 'System' }}<br><span class="text-muted">{{ $task->created_at->format('d-M-y') }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-xs btn-info" title="View & Workflow">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if(Auth::user()->hasRole(['Admin', 'Owner']) || Auth::id() == $task->created_by)
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-xs btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete Task?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4">No Tasks Found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $tasks->links() }}</div>
</div>
@endsection