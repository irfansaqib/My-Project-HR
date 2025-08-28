@extends('layouts.admin')
@section('title', 'Apply for Leave')
@section('content')

@if ($employee->total_leaves_remaining > 0)
    {{-- Show the standard leave form if the employee has leaves left --}}
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">New Leave Application</h3>
            <div class="card-tools">
                <a href="{{ route('leave-requests.extra-create') }}" class="btn btn-warning btn-sm disabled" title="This is enabled when all your leaves are used">Request Extra Leave</a>
            </div>
        </div>
        <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
            @csrf
            @include('leave-requests._form')
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@else
    {{-- Show a message and the enabled "Extra Leave" button if they have no leaves left --}}
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">New Leave Application</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h4>Out of Leaves</h4>
                <p>Your allocated leave balance is zero. You can submit a special request for extra leave by clicking the button below.</p>
            </div>
             <a href="{{ route('leave-requests.extra-create') }}" class="btn btn-warning">Request Extra Leave</a>
             <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Back to My Leaves</a>
        </div>
    </div>
@endif

@endsection