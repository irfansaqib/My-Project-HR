@extends('layouts.admin')
@section('title', 'Edit Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Edit Task: {{ $task->task_number }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                    @csrf @method('PUT')
                    @include('tasks._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection