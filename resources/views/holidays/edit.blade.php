@extends('layouts.admin')
@section('title', 'Edit Holiday')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Holiday: {{ $holiday->title }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('holidays.update', $holiday->id) }}" method="POST">
            @method('PUT')
            @include('holidays._form', ['holiday' => $holiday, 'buttonText' => 'Update Holiday'])
        </form>
    </div>
</div>
@endsection