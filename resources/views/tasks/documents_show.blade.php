@extends('layouts.admin')

@section('title', 'Client Documents')

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-dark">
                <span class="text-muted">Documents for:</span> 
                {{ $client->business_name }}
            </h4>
            <small class="text-muted">
                {{ $client->cnic ?? $client->registration_number ?? 'No ID' }}
            </small>
        </div>
        <a href="{{ route('admin.documents.clients') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Clients
        </a>
    </div>

    {{-- Content --}}
    @if($tasks->isEmpty())
        <div class="text-center py-5 card shadow-sm">
            <div class="card-body">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No documents found for this client.</h5>
            </div>
        </div>
    @else
        @foreach($tasks as $task)
            <div class="card shadow-sm mb-4">
                {{-- Task Header --}}
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        {{-- Task Number Badge --}}
                        <span class="badge bg-primary me-2">
                            {{ $task->task_number ?? 'Task #' . $task->id }}
                        </span>
                        <span class="fw-bold">{{ $task->description ?? 'Task Details' }}</span>
                    </div>
                    <span class="text-muted small">
                        {{ $task->created_at->format('d M, Y') }}
                    </span>
                </div>

                {{-- Documents List --}}
                <div class="card-body p-0">
                    @if($task->documents->isEmpty())
                        <div class="p-3 text-center text-muted small fst-italic">
                            No documents attached.
                        </div>
                    @else
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-white text-muted small">
                                <tr>
                                    <th class="ps-4">File Name</th>
                                    <th class="text-end pe-4">Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->documents as $doc)
                                <tr>
                                    <td class="ps-4">
                                        <i class="fas fa-file-alt text-secondary me-2"></i>
                                        {{ $doc->title ?? basename($doc->file_path) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary" 
                                           download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection