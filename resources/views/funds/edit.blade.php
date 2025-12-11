@extends('layouts.admin')
@section('title', 'Edit Fund')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Fund Configuration</h3>
    </div>
    <form action="{{ route('funds.update', $fund->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('funds._form')
    </form>
</div>
@endsection