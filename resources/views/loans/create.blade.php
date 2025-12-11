@extends('layouts.admin')
@section('title', 'New Loan / Advance')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Request New Loan or Salary Advance</h3>
    </div>
    <form action="{{ route('loans.store') }}" method="POST">
        @csrf
        @include('loans._form')
    </form>
</div>
@endsection