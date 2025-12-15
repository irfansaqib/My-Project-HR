@extends('layouts.admin')
@section('title', 'Edit Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="m-0"><i class="fas fa-edit mr-2"></i> Edit Category: {{ $taskCategory->name }}</h5>
            </div>
            
            {{-- Note: Use $taskCategory because Route::resource passes the model variable automatically --}}
            <form action="{{ route('task-categories.update', $taskCategory->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                {{-- Include the shared form, passing the existing category --}}
                @include('task_categories._form', [
                    'category' => $taskCategory, 
                    'buttonText' => 'Update Category'
                ])
            </form>
        </div>
    </div>
</div>
@endsection