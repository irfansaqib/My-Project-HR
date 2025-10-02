@extends('layouts.admin')
@section('title', 'Issue Warning to ' . $employee->name)

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Issue Disciplinary Warning</h3>
    </div>
    <form action="{{ route('warnings.store', $employee->id) }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Employee</label>
                <input type="text" class="form-control" value="{{ $employee->name }} ({{ $employee->employee_number }})" readonly>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="warning_date">Date of Warning <span class="text-danger">*</span></label>
                    <input type="date" name="warning_date" id="warning_date" class="form-control @error('warning_date') is-invalid @enderror" value="{{ old('warning_date', now()->format('Y-m-d')) }}" required>
                    @error('warning_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="subject">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="e.g., Unprofessional Conduct" required>
                    @error('subject')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description of Incident <span class="text-danger">*</span></label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            {{-- ✅ NEW: Action Taken Field --}}
            <div class="form-group">
                <label for="action_taken">Action Taken / Recommendation (Optional)</label>
                <textarea name="action_taken" id="action_taken" class="form-control @error('action_taken') is-invalid @enderror" rows="3">{{ old('action_taken') }}</textarea>
                @error('action_taken')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
             <div class="form-group">
                <label>Issued By</label>
                <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Issue Warning</button>
            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection