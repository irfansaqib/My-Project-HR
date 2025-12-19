@extends('layouts.client_portal')

@section('header', 'Task Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        
        {{-- TASK DETAILS CARD --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary">{{ $task->task_number ?? '#' . $task->id }}</h5>
                    <span class="badge bg-light text-dark border">{{ $task->category->name ?? 'General' }}</span>
                </div>
            </div>
            <div class="card-body">
                <h6 class="text-uppercase text-muted small fw-bold mb-2">Description</h6>
                <p class="mb-4 text-dark" style="white-space: pre-wrap;">{{ $task->description }}</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Created Date</h6>
                        <p class="fw-medium">{{ $task->created_at->format('d M, Y h:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Preferred Due Date</h6>
                        <p class="fw-medium text-danger">
                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M, Y') : 'Not Specified' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- DISCUSSION AREA --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold"><i class="fas fa-comments me-2"></i> Discussion & Updates</h6>
            </div>
            
            {{-- CHAT LOOP --}}
            <div class="card-body bg-light" style="max-height: 400px; overflow-y: auto;">
                
                @forelse($task->messages as $message)
                    <div class="d-flex mb-3 {{ $message->sender_id == Auth::id() ? 'justify-content-end' : '' }}">
                        <div class="card border-0 shadow-sm p-3 {{ $message->sender_id == Auth::id() ? 'bg-primary text-white' : 'bg-white' }}" style="max-width: 75%;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-bold {{ $message->sender_id == Auth::id() ? 'text-light' : 'text-primary' }}">
                                    {{ $message->sender_id == Auth::id() ? 'You' : $message->sender->name }}
                                </small>
                                <small class="{{ $message->sender_id == Auth::id() ? 'text-white-50' : 'text-muted' }} ms-3" style="font-size: 0.75rem;">
                                    {{ $message->created_at->diffForHumans() }}
                                </small>
                            </div>
                            
                            {{-- Message Text --}}
                            <p class="mb-0">{{ $message->message }}</p>

                            {{-- Attachment Display --}}
                            @if($message->attachment_path)
                                <div class="mt-2 pt-2 border-top {{ $message->sender_id == Auth::id() ? 'border-white-50' : 'border-light' }}">
                                    <a href="{{ Storage::url($message->attachment_path) }}" target="_blank" 
                                       class="text-decoration-none small {{ $message->sender_id == Auth::id() ? 'text-light' : 'text-primary' }}">
                                        <i class="fas fa-paperclip me-1"></i> 
                                        {{ $message->file_name ?? 'View Attachment' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="far fa-comment-dots fa-2x mb-2"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                @endforelse

            </div>
            
            {{-- SEND FORM --}}
            <div class="card-footer bg-white p-3">
                <form action="{{ route('client.tasks.messages.store', $task->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        {{-- Attachment Button --}}
                        <label class="btn btn-light border mb-0" title="Attach File" style="cursor: pointer;">
                            <i class="fas fa-paperclip text-secondary"></i> 
                            <input type="file" name="attachment" hidden onchange="this.parentElement.classList.add('bg-warning')">
                        </label>

                        <input type="text" name="message" class="form-control" placeholder="Type a message or reply..." required>
                        
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- SIDEBAR STATUS --}}
    <div class="col-lg-4">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Current Status</h6>
                @php
                    $statusClass = match($task->status) {
                        'Completed' => 'success',
                        'In Progress' => 'primary',
                        'Pending' => 'secondary',
                        default => 'dark',
                    };
                @endphp
                <div class="alert alert-{{ $statusClass }} text-center mb-0 fw-bold">
                    {{ strtoupper($task->status) }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Priority Level</h6>
                @php
                    $priorityIcon = match($task->priority) {
                        'Very Urgent' => 'fire',
                        'Urgent' => 'exclamation-circle',
                        default => 'check-circle',
                    };
                    $priorityColor = match($task->priority) {
                        'Very Urgent' => 'danger',
                        'Urgent' => 'warning',
                        default => 'success',
                    };
                @endphp
                <div class="d-flex align-items-center text-{{ $priorityColor }}">
                    <i class="fas fa-{{ $priorityIcon }} fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold">{{ $task->priority }}</h5>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            @if($task->status == 'Pending')
                <a href="{{ route('client.tasks.edit', $task->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-edit me-2"></i> Edit Request
                </a>
            @endif
            <a href="{{ route('client.tasks.index') }}" class="btn btn-light border">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>

    </div>
</div>
@endsection