@extends('layouts.admin')
@section('title', 'Edit Loan / Advance')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Edit Loan Record: {{ $loan->employee->name }}</h3>
    </div>
    
    {{-- ✅ FIX: Added 'novalidate' to bypass browser validation blocks --}}
    <form action="{{ route('loans.update', $loan->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        
        {{-- Status Field (Specific to Edit) --}}
        <div class="card-body border-bottom pb-0">
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" style="width: 200px;">
                    <option value="pending" {{ $loan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="running" {{ $loan->status == 'running' ? 'selected' : '' }}>Running</option>
                    <option value="completed" {{ $loan->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $loan->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>
        
        {{-- Include the common form fields --}}
        @include('loans._form')
        
    </form>
</div>
@endsection