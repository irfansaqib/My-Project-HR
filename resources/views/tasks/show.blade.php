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

                {{-- ADMIN / CREATOR ACTIONS --}}
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
                    
                    {{-- ADD SUPERVISOR --}}
                    <hr>
                    <button class="btn btn-link btn-sm pl-0 text-dark font-weight-bold" onclick="$('#supervisorBox').slideToggle()">
                        <i class="fas fa-user-plus mr-1"></i> Add Supervisor
                    </button>
                    <div id="supervisorBox" style="display:none;" class="mt-2 mb-2">
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

                    {{-- EXTEND DUE DATE BUTTON --}}
                    <button class="btn btn-outline-warning btn-block btn-sm mt-2 text-dark font-weight-bold" 
                            data-toggle="modal" data-target="#extendModal">
                        <i class="fas fa-calendar-plus mr-1"></i> Extend Due Date
                    </button>

                    {{-- EXTENSION HISTORY (Small list below buttons) --}}
                    @if($task->extensions && $task->extensions->count() > 0)
                        <div class="mt-3 bg-light p-2 rounded border small">
                            <strong class="text-muted">Extension History:</strong>
                            <ul class="pl-3 mb-0 mt-1 text-muted" style="list-style: none;">
                                @foreach($task->extensions as $ext)
                                    <li class="mb-1">
                                        <i class="fas fa-history text-warning mr-1"></i>
                                        {{ $ext->old_due_date->format('d M') }} <i class="fas fa-arrow-right mx-1"></i> {{ $ext->new_due_date->format('d M') }}
                                        <br><span style="font-size: 10px; margin-left: 15px;">by {{ $ext->changer->name ?? 'Admin' }} ({{ $ext->created_at->format('d M') }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
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
                        
                        {{-- ✅ UPDATED CATEGORY SECTION (NULL-SAFE) --}}
                        <tr class="border-bottom bg-light">
                            <td class="pl-3 py-2 text-muted small font-weight-bold">Category</td>
                            <td class="py-2 small">
                                {{-- Use ?-> operator to prevent crashing if parent is missing --}}
                                <div class="text-muted">{{ $task->category?->parent?->parent?->name }}</div>
                                <div class="text-muted">{{ $task->category?->parent?->name }}</div>
                                <div class="text-dark font-weight-bold">{{ $task->category?->name ?? 'Uncategorized' }}</div>
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
                            <td class="py-2 text-danger font-weight-bold">
                                {{ $task->due_date ? $task->due_date->format('d M, Y h:i A') : '-' }}
                                @if(method_exists($task, 'isOverdue') && $task->isOverdue())
                                    <span class="badge badge-danger ml-1">OVERDUE</span>
                                @endif
                            </td>
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

{{-- MODALS SECTION --}}
{{-- EXTEND DATE MODAL --}}
<div class="modal fade" id="extendModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h6 class="modal-title text-dark font-weight-bold">Extend Deadline</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('tasks.extend', $task->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Current Due Date</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $task->due_date ? $task->due_date->format('d M Y, h:i A') : 'N/A' }}" disabled>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">New Due Date</label>
                        <input type="datetime-local" name="new_due_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Reason for Extension</label>
                        <textarea name="reason" rows="2" class="form-control form-control-sm" placeholder="e.g. Client delayed data..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button class="btn btn-warning btn-block btn-sm font-weight-bold">Confirm Extension</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection