@extends('layouts.admin')
@section('title', 'Task Performance & Analytics')

@section('content')
<style>
    /* Status Font Colors & Styles */
    .status-row-pending td { color: #dc3545; font-weight: 500; } /* Red */
    .status-row-progress td { color: #007bff; font-weight: 600; } /* Blue */
    .status-row-executed td { color: #17a2b8; font-weight: 600; } /* Info/Cyan */
    .status-row-completed td { color: #28a745; font-weight: 600; } /* Green */
    .status-row-closed td { color: #155724; font-weight: 600; } /* Dark Green */
    
    /* Overdue Overrides (applies to the specific Overdue Tag primarily) */
    .overdue-tag { font-size: 10px; letter-spacing: 0.5px; vertical-align: middle; }
    
    /* Keep Action Button Normal Color */
    .table td.action-cell { color: inherit !important; }
</style>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-filter mr-2 text-primary"></i> Filter Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('tasks.report') }}">
            <div class="row">
                {{-- Row 1: People & Status --}}
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">Assigned To (Employee)</label>
                    <select name="employee_id" class="form-control form-control-sm select2">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">Assigned By</label>
                    <select name="assigned_by" class="form-control form-control-sm select2">
                        <option value="">All Assigners</option>
                        @foreach($assigners as $u)
                            <option value="{{ $u->id }}" {{ request('assigned_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">Client</label>
                    <select name="client_id" class="form-control form-control-sm select2">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->business_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold">Current Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Process</option>
                        <option value="Executed" {{ request('status') == 'Executed' ? 'selected' : '' }}>Executed</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed/Closed</option>
                        <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
            </div>

            <div class="row">
                {{-- Row 2: Dates --}}
                <div class="col-md-3">
                    <label class="small font-weight-bold">Assigned On Date</label>
                    <input type="date" name="assigned_date" class="form-control form-control-sm" value="{{ request('assigned_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">Due Date</label>
                    <input type="date" name="due_date" class="form-control form-control-sm" value="{{ request('due_date') }}">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm px-4 mr-2"><i class="fas fa-search mr-1"></i> Apply Filters</button>
                    <a href="{{ route('tasks.report') }}" class="btn btn-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- STATS ROW --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Tasks</span>
                <span class="info-box-number">{{ $stats['total'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">In Process</span>
                <span class="info-box-number">{{ $stats['in_progress'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Completed</span>
                <span class="info-box-number">{{ $stats['completed'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Overdue</span>
                <span class="info-box-number">{{ $stats['overdue'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- DATA TABLE --}}
<div class="card shadow-sm">
    <div class="card-header bg-white border-0">
        <h6 class="m-0 font-weight-bold text-dark">Detailed Results</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th>Task #</th>
                        <th>Assigned To</th>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Assigned On</th>
                        <th>Due Date</th>
                        <th style="min-width: 140px;">Status</th>
                        <th>Time Spent</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    @php
                        // Determine Row Class for Color (Base font color)
                        $rowClass = 'status-row-pending';
                        if($task->status == 'In Progress') $rowClass = 'status-row-progress';
                        elseif($task->status == 'Executed') $rowClass = 'status-row-executed';
                        elseif($task->status == 'Completed') $rowClass = 'status-row-completed';
                        elseif($task->status == 'Closed') $rowClass = 'status-row-closed';
                        
                        // If overdue, we might want the whole row red, OR just the status column.
                        // Using your previous request: font color based on status.
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $task->task_number }}</td>
                        <td>{{ $task->assignedEmployee->name ?? 'Unassigned' }}</td>
                        <td>{{ $task->client->business_name }}</td>
                        <td>
                            <span class="d-block small text-muted" style="line-height:1;">{{ $task->category->parent->name ?? '' }}</span>
                            {{ $task->category->name }}
                        </td>
                        <td>{{ $task->created_at->format('d-M-y') }}</td>
                        <td>{{ $task->due_date ? $task->due_date->format('d-M-y') : '-' }}</td>
                        
                        {{-- STATUS COLUMN with Overdue Tag --}}
                        <td>
                            <div>{{ $task->status }}</div>
                            
                            @if($task->isOverdue())
                                <span class="badge badge-danger overdue-tag">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Overdue
                                </span>
                            @endif
                            
                            @if(method_exists($task, 'extensionCount') && $task->extensionCount() > 0)
                                <div class="text-muted small" style="font-size: 10px; line-height: 1;">
                                    <i class="fas fa-history mr-1"></i> Ext ({{ $task->extensionCount() }})
                                </div>
                            @endif
                        </td>
                        
                        <td>{{ $task->formattedTotalTime() }}</td>
                        <td class="action-cell">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0">
        {{ $tasks->withQueryString()->links() }}
    </div>
</div>
@endsection