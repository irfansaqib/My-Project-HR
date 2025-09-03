@extends('layouts.admin')
@section('title', 'Add New Role')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Role</h3>
    </div>
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        @include('roles._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Role</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection