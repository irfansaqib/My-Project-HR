@extends('layouts.admin')
@section('title', 'Generate Salary Sheet')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Generate Salaries for a Month</h3>
    </div>
    {{-- ✅ FIX: Changed the form action to the correct 'salaries.generate' route --}}
    <form action="{{ route('salaries.generate') }}" method="POST">
        @csrf
        <div class="card-body">
            <p class="text-muted">Select the month and year for which you want to generate salary slips for all active employees. This process will calculate salaries, taxes, and deductions based on the current employee records.</p>
            
            <div class="form-group">
                <label for="month">Select Month and Year <span class="text-danger">*</span></label>
                <input type="month" name="month" id="month" class="form-control @error('month') is-invalid @enderror" value="{{ old('month', now()->format('Y-m')) }}" required>
                @error('month')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Generate Salary Sheet</button>
            <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection