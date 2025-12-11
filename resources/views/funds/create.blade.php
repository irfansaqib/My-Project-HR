@extends('layouts.admin')
@section('title', 'Setup New Fund')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Setup Contributory Fund</h3>
    </div>
    <form action="{{ route('funds.store') }}" method="POST">
        @csrf
        @include('funds._form')
    </form>
</div>
@endsection