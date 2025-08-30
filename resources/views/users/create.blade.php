@extends('layouts.admin')

@section('title', 'Add New User')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New User</h3>
    </div>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('users._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection