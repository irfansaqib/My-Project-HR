@extends('layouts.admin')
@section('title', 'Edit Department')
@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Department</h3>
    </div>
    <form method="POST" action="{{ route('departments.update', $department) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label for="name">Department Name</label>
                <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $department->name) }}" required>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection