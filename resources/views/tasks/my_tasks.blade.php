@extends('layouts.admin')
@section('title', 'My Tasks')

@section('content')
<style>
    /* Status Colors based on PDF */
    .status-pending { background-color: #ffcccc; color: #cc0000; } /* Light Red */
    .status-progress { background-color: #cce5ff; color: #004085; } /* Light Blue */
    .status-completed { background-color: #fff3cd; color: #856404; } /* Light Yellow */
    .status-closed { background-color: #d4edda; color: #155724; } /* Light Green */
    .status-overdue { border: 2px solid #dc3545; color: #dc3545; } /* Red Border */
</style>

<div class="container-fluid">

    {{-- SEGMENT 1: ASSIGNED TODAY --}}
    <div class="card shadow-sm mb-4 border-left-primary">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-inbox mr-2"></i> Tasks Assigned Today</h5>
        </div>
        <div class="card-body p-0">
            @if($assignedToday->isEmpty())
                <div class="p-4 text-center text-muted">No new tasks assigned today.</div>
            @else
                @include('tasks.partials.task_table', ['tasks' => $assignedToday])
            @endif
        </div>
    </div>

    {{-- SEGMENT 2: DUE TODAY --}}
    <div class="card shadow-sm mb-4 border-left-danger">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 font-weight-bold text-danger"><i class="fas fa-hourglass-half mr-2"></i> Due Today</h5>
        </div>
        <div class="card-body p-0">
            @if($dueToday->isEmpty())
                <div class="p-4 text-center text-muted">No tasks due today.</div>
            @else
                @include('tasks.partials.task_table', ['tasks' => $dueToday])
            @endif
        </div>
    </div>

    {{-- SEGMENT 3: ALL MY TASKS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="m-0 font-weight-bold">All My Tasks</h5>
        </div>
        <div class="card-body p-0">
            @include('tasks.partials.task_table', ['tasks' => $allMyTasks])
        </div>
        <div class="card-footer">
            {{ $allMyTasks->links() }}
        </div>
    </div>

</div>
@endsection