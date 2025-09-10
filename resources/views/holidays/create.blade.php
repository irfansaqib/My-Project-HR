@extends('layouts.admin')
@section('title', 'Add Holiday')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Holiday</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('holidays.store') }}" method="POST">
            @include('holidays._form', ['buttonText' => 'Save Holiday'])
        </form>
    </div>
</div>
@endsection