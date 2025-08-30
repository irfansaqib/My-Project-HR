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
        @include('users._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection