@extends('layouts.admin')
@section('title', 'Request Leave Encashment')

@section('content')
<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> New Encashment Request</h3>
    </div>
    <form action="{{ route('leave-encashments.store') }}" method="POST">
        <div class="card-body">
            @include('leave-encashments._form', ['buttonText' => 'Submit Request'])
        </div>
    </form>
</div>
@endsection