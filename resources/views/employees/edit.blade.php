@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Employee: {{ $employee->name }}</h3>
    </div>
    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
        @include('employees._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Employee</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection