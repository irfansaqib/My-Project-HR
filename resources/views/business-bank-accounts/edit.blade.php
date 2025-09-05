@extends('layouts.admin')
@section('title', 'Edit Bank Account')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Edit Bank Account</h3></div>
    <form action="{{ route('business-bank-accounts.update', $businessBankAccount) }}" method="POST">
        @csrf
        @method('PUT')
        @include('business-bank-accounts._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Account</button>
            <a href="{{ route('business-bank-accounts.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection