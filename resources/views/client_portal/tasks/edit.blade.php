@extends('layouts.client_portal')
@section('header', 'Edit Request: ' . $task->task_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                {{-- Validation Check --}}
                @if($task->status != 'Pending')
                    <div class="alert alert-warning">
                        <i class="fas fa-lock me-2"></i> This task is <strong>{{ $task->status }}</strong>. Editing may disrupt the workflow.
                    </div>
                @endif

                <form action="{{ route('client.tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    @include('client_portal.tasks._form', [
                        'task' => $task,
                        'categories' => $categories,
                        'selectedLvl0' => $selectedLvl0,
                        'selectedLvl1' => $selectedLvl1,
                        'selectedLvl2' => $selectedLvl2
                    ])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection