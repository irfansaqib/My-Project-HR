@extends('layouts.admin')
@section('title', 'Edit Salary Component')
@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Edit: {{ $salaryComponent->name }}</h3></div>
    <form action="{{ route('salary-components.update', $salaryComponent) }}" method="POST">
        @csrf
        @method('PATCH')
        
        @include('salary-components._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Component</button>
            <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection