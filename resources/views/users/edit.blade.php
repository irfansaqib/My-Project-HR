@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit User: {{ $user->name }}</h3>
    </div>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" class="form-control" id="name" placeholder="Enter full name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Leave blank to keep current password">
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm new password">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection