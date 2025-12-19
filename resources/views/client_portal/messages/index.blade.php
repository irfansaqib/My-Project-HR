{{-- ✅ Pointing to the correct layout file --}}
@extends('layouts.client_portal') 

@section('header', 'Message Center')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($tasks as $task)
                    <a href="{{ route('client.tasks.show', $task->id) }}" class="list-group-item list-group-item-action p-4 border-bottom">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                            <h5 class="mb-0 text-primary fw-bold">
                                {{ $task->task_number }}
                                <span class="badge bg-{{ $task->status == 'Completed' ? 'success' : 'info' }} ms-2" style="font-size: 0.6em">
                                    {{ $task->status }}
                                </span>
                            </h5>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i> 
                                {{ $task->messages->first() ? $task->messages->first()->created_at->diffForHumans() : 'Just now' }}
                            </small>
                        </div>
                        
                        <p class="mb-2 text-dark text-opacity-75">
                            {{ \Illuminate\Support\Str::limit($task->description, 100) }}
                        </p>
                        
                        <div class="d-flex align-items-center mt-3">
                            <small class="text-secondary me-4">
                                <i class="fas fa-comment-alt me-1"></i> 
                                {{ $task->messages->count() }} Messages
                            </small>
                            
                            @if($task->messages->whereNotNull('attachment')->count() > 0)
                                <small class="text-secondary">
                                    <i class="fas fa-paperclip me-1"></i> 
                                    {{ $task->messages->whereNotNull('attachment')->count() }} Attachments
                                </small>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5">
                        <div class="text-muted mb-3 opacity-50"><i class="far fa-comment-dots fa-4x"></i></div>
                        <h5 class="fw-bold text-secondary">No messages yet</h5>
                        <p class="text-muted">Start a "New Request" to begin a conversation.</p>
                        <a href="{{ route('client.tasks.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus-circle me-1"></i> Create Request
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection