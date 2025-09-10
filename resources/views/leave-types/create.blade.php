@extends('layouts.admin')
@section('title', 'Create Leave Type')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Leave Type</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('leave-types.store') }}" method="POST">
            @include('leave-types._form', ['buttonText' => 'Save'])
        </form>
    </div>
</div>
@endsection