@extends('layouts.admin')

@section('title', 'Add Designation')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Designation</h3>
    </div>
    <form method="POST" action="{{ route('designations.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="name">Designation Name</label>
                <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" required>
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('designations.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection