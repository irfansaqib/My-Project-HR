@extends('layouts.admin')

@section('title', 'Add New Employee')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Employee</h3>
    </div>
    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        
        @include('employees._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Employee</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection