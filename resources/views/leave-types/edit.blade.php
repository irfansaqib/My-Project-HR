@extends('layouts.admin')
@section('title', 'Edit Leave Type')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Leave Type</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('leave-types.update', $leaveType->id) }}" method="POST">
            @method('PUT')
            @include('leave-types._form', ['leaveType' => $leaveType, 'buttonText' => 'Update'])
        </form>
    </div>
</div>
@endsection