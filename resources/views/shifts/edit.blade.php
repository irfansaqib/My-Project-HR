@extends('layouts.admin')
@section('title', 'Edit Shift')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Shift: {{ $shift->shift_name }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('shifts.update', $shift->id) }}" method="POST">
            @method('PUT')
            @include('shifts._form', ['shift' => $shift, 'buttonText' => 'Update Shift'])
        </form>
    </div>
</div>
@endsection