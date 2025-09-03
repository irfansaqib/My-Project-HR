@extends('layouts.admin')
@section('title', 'Edit Role')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Role: {{ $role->name }}</h3>
    </div>
    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PATCH')
        @include('roles._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Role</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection