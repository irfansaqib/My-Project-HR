@extends('layouts.admin')
@section('title', 'Task Details: ' . $task->task_number)

@section('content')
<div class="row">
    {{-- LEFT COLUMN: ACTIONS & METADATA --}}
    <div class="col-md-4">
        
        {{-- 1. TIMER WIDGET (Only for Assignee when In Progress) --}}
        @if($task->status == 'In Progress' && Auth::user()->employee && Auth::user()->employee->id == $task->assigned_to)
        <div class="card shadow-sm border-left-info mb-3">
            <div class="card-body text-center p-3">
                <h6 class="text-uppercase small font-weight-bold text-muted mb-2">Time Tracker</h6>
                <h2 class="font-weight-bold text-dark mb-3">{{ $task->formattedTotalTime() }}</h2>
                @if($task->activeTimer)
                    <div class="alert alert-success py-1 small mb-2"><i class="fas fa-clock fa-spin mr-1"></i> Timer Running...</div>
                    <form action="{{ route('tasks.timer.stop', $task->id) }}" method="POST"> @csrf
                        <button class="btn btn-danger btn-block font-weight-bold"><i class="fas fa-stop mr-1"></i> Stop Timer</button>
                    </form>
                @else
                    <form action="{{ route('tasks.timer.start', $task->id) }}" method="POST"> @csrf
                        <button class="btn btn-success btn-block font-weight-bold"><i class="fas fa-play mr-1"></i> Start Timer</button>
                    </form>
                @endif
            </div>
        </div>
        @endif

        {{-- 2. WORKFLOW ACTIONS PANEL --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="m-0 font-weight-bold small"><i class="fas fa-cogs mr-1"></i> Actions Panel</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small font-weight-bold">Current Status:</span>
                    <span class="badge badge-{{ $task->status == 'Completed' ? 'success' : ($task->status == 'Overdue' ? 'danger' : 'primary') }} px-2 py-1">
                        {{ $task->status }}
                    </span>
                </div>

                {{-- EMPLOYEE ACTIONS --}}
                @if(Auth::user()->employee && Auth::user()->employee->id == $task->assigned_to)
                    @if($task->status == 'Pending')
                        <form action="{{ route('tasks.employee.accept', $task->id) }}" method="POST" class="mb-2"> @csrf
                            <button class="btn btn-success btn-block shadow-sm"><i class="fas fa-check mr-2"></i> Accept Task</button>
                        </form>
                        <button class="btn btn-outline-danger btn-block btn-sm" onclick="$('#empReject').slideToggle()">Reject</button>
                        <form id="empReject" action="{{ route('tasks.employee.reject', $task->id) }}" method="POST" style="display:none;" class="mt-2">
                            @csrf
                            <textarea name="reason" class="form-control form-control-sm mb-1" placeholder="Reason..." required></textarea>
                            <button class="btn btn-danger btn-xs btn-block">Confirm</button>
                        </form>
                    @elseif($task->status == 'In Progress')
                        <form action="{{ route('tasks.execute', $task->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Mark as done?');"> @csrf
                            <button class="btn btn-primary btn-block shadow-sm"><i class="fas fa-check-double mr-2"></i> Mark Executed</button>
                        </form>
                        <button class="btn btn-outline-secondary btn-block btn-sm" onclick="$('#reassignBox').slideToggle()"><i class="fas fa-share mr-1"></i> Re-Assign</button>
                        <div id="reassignBox" style="display:none;" class="mt-2 bg-light p-2 border rounded">
                            <form action="{{ route('tasks.reassign', $task->id) }}" method="POST"> @csrf
                                <select name="new_employee_id" class="form-control form-control-sm mb-2">
                                    @foreach(\App\Models\Employee::where('status', 'active')->where('id', '!=', Auth::user()->employee->id)->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-warning btn-xs btn-block text-dark">Delegate</button>
                            </form>
                        </div>
                    @endif

                {{-- ADMIN ACTIONS --}}
                @elseif(Auth::user()->hasRole(['Admin', 'Owner']) || Auth::id() == $task->created_by)
                    @if($task->status == 'Executed')
                        <div class="alert alert-info small mb-2"><i class="fas fa-info-circle"></i> Review Required</div>
                        <form action="{{ route('tasks.finalize', $task->id) }}" method="POST" class="mb-2"> @csrf
                            <button class="btn btn-success btn-block"><i class="fas fa-gavel mr-2"></i> Finalize</button>
                        </form>
                        <button class="btn btn-outline-danger btn-block btn-sm" onclick="$('#adminReject').slideToggle()">Send Back (Redo)</button>
                        <form id="adminReject" action="{{ route('tasks.admin.reject', $task->id) }}" method="POST" style="display:none;" class="mt-2">
                            @csrf
                            <textarea name="reason" class="form-control form-control-sm mb-1" placeholder="Reason..." required></textarea>
                            <button class="btn btn-danger btn-xs btn-block">Confirm Return</button>
                        </form>
                    @endif
                    
                    <hr>
                    <button class="btn btn-link btn-sm pl-0 text-dark font-weight-bold" onclick="$('#supervisorBox').slideToggle()">
                        <i class="fas fa-user-plus mr-1"></i> Add Supervisor
                    </button>
                    <div id="supervisorBox" style="display:none;" class="mt-2">
                        <form action="{{ route('tasks.supervisor', $task->id) }}" method="POST"> @csrf
                            <div class="input-group">
                                <select name="supervisor_id" class="form-control form-control-sm">
                                    <option value="">-- Select --</option>
                                    @foreach(\App\Models\User::role(['Admin', 'Owner'])->get() as $u)
                                        <option value="{{ $u->id }}" {{ $task->supervisor_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append"><button class="btn btn-secondary btn-sm">Save</button></div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. DETAILED INFO CARD --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="m-0 font-weight-bold text-primary small">Task Information</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold" width="35%">Task #</td>
                            <td class="py-2 text-dark font-weight-bold">{{ $task->task_number }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Priority</td>
                            <td class="py-2">
                                @php $pColor = match($task->priority) { 'Very Urgent'=>'danger', 'Urgent'=>'warning', default=>'success' }; @endphp
                                <span class="badge badge-{{ $pColor }}">{{ $task->priority }}</span>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Client</td>
                            <td class="py-2 text-primary font-weight-bold">{{ $task->client->business_name }}</td>
                        </tr>
                        <tr class="border-bottom bg-light">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Category</td>
                            <td class="py-2 small">
                                <div class="text-muted">{{ $task->category->parent->parent->name ?? '' }}</div>
                                <div class="text-muted">{{ $task->category->parent->name ?? '' }}</div>
                                <div class="text-dark font-weight-bold">{{ $task->category->name }}</div>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Assigned To</td>
                            <td class="py-2">{{ $task->assignedEmployee->name ?? 'Unassigned' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Assigned By</td>
                            <td class="py-2">{{ $task->creator->name ?? 'System' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Supervisor</td>
                            <td class="py-2">{{ $task->supervisor->name ?? 'None' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Start Date</td>
                            <td class="py-2 text-success">{{ $task->start_date ? $task->start_date->format('d M, Y h:i A') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Due Date</td>
                            <td class="py-2 text-danger font-weight-bold">{{ $task->due_date ? $task->due_date->format('d M, Y h:i A') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN: DESCRIPTION & CHAT --}}
    <div class="col-md-8">
        
        {{-- DESCRIPTION BOX --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="m-0 font-weight-bold text-dark small">Task Description / Instructions</h6>
            </div>
            <div class="card-body bg-light text-dark border-left-primary" style="border-left: 4px solid #4e73df;">
                {!! nl2br(e($task->description)) !!}
            </div>
        </div>

        {{-- CHAT INTERFACE --}}
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-2"></i> Communication History</h6>
                <span class="badge badge-light border">{{ $task->messages->count() }} Messages</span>
            </div>
            <div class="card-body bg-light">
                @include('tasks.partials.chat')
            </div>
        </div>
    </div>
</div>
@endsection