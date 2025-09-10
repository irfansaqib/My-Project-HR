@extends('layouts.admin')
@section('title', 'Create Shift')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Shift</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('shifts.store') }}" method="POST">
            @include('shifts._form', ['buttonText' => 'Save Shift'])
        </form>
    </div>
</div>
@endsection