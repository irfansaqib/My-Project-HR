@extends('layouts.client_portal') 

@section('header', 'My Documents')

@section('content')

@php
    // 1. Get the Client ID
    $clientId = \App\Models\Client::where('user_id', Auth::id())->value('id');
    
    // 2. Fetch Tasks and their documents
    $tasks = \App\Models\Task::where('client_id', $clientId)
                    ->with('documents')     
                    ->orderBy('id', 'desc')
                    ->get();
@endphp

<div class="container-fluid">

    @if($tasks->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-clipboard-list fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted">No tasks found.</h5>
        </div>
    @else

        @foreach($tasks as $task)
            <div class="card card-stat mb-4">
                
                {{-- Task Header --}}
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <div>
                        {{-- UPDATED: Badge now shows Auto-Generated Task Number --}}
                        <span class="badge bg-primary me-2" style="font-size: 0.9rem;">
                            {{ $task->task_number ?? 'Task #' . $task->id }}
                        </span>

                        <h6 class="d-inline-block fw-bold mb-0 text-dark">
                            {{ $task->description ?? 'Task Details' }}
                        </h6>
                    </div>
                    <span class="text-muted small">
                        <i class="far fa-clock me-1"></i> {{ $task->created_at->format('d M, Y') }}
                    </span>
                </div>

                {{-- Documents List --}}
                <div class="card-body p-0">
                    
                    @if($task->documents->isEmpty())
                        <div class="text-center py-3">
                            <span class="text-muted small fst-italic">No documents attached to this task yet.</span>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Document Name</th>
                                        <th>Upload Date</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->documents as $doc)
                                        <tr>
                                            {{-- Document Name --}}
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 text-secondary">
                                                        {{-- Icon Logic --}}
                                                        @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                                                        @if(in_array(strtolower($ext), ['pdf']))
                                                            <i class="fas fa-file-pdf fa-lg text-danger"></i>
                                                        @elseif(in_array(strtolower($ext), ['jpg','jpeg','png']))
                                                            <i class="fas fa-file-image fa-lg text-success"></i>
                                                        @elseif(in_array(strtolower($ext), ['doc','docx']))
                                                            <i class="fas fa-file-word fa-lg text-primary"></i>
                                                        @elseif(in_array(strtolower($ext), ['xls','xlsx']))
                                                            <i class="fas fa-file-excel fa-lg text-success"></i>
                                                        @else
                                                            <i class="fas fa-file-alt fa-lg"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        {{-- Title Logic --}}
                                                        <span class="fw-medium">{{ $doc->title ?? basename($doc->file_path) }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Date --}}
                                            <td class="text-secondary">
                                                {{ $doc->created_at->format('d M, Y h:i A') }}
                                            </td>

                                            {{-- Download --}}
                                            <td class="text-end pe-4">
                                                <a href="{{ asset('storage/' . $doc->file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   download>
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

    @endif
</div>

@endsection