@extends('layouts.admin')
@section('title', 'Add Bank Account')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Add New Bank Account</h3></div>
    <form action="{{ route('business-bank-accounts.store') }}" method="POST">
        @csrf
        @include('business-bank-accounts._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Account</button>
            <a href="{{ route('business-bank-accounts.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection