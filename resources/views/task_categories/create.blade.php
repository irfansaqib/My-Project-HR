@extends('layouts.admin')
@section('title', 'Add New Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0"><i class="fas fa-plus-circle mr-2"></i> Add New Task Category</h5>
            </div>
            
            <form action="{{ route('task-categories.store') }}" method="POST">
                @csrf
                @include('task_categories._form', ['buttonText' => 'Create Category'])
            </form>
        </div>
    </div>
</div>
@endsection