@extends('layouts.admin')
@section('title', 'Process Employee Exit')

@section('content')
<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title">Process Exit for: {{ $employee->name }}</h3>
    </div>
    <form action="{{ route('employees.exit.store', $employee->id) }}" method="POST">
        @csrf
        <div class="card-body">
            <p class="text-muted">Use this form to formally record an employee's departure. Once processed, the employee's status will be updated, and they will be excluded from future payroll runs.</p>

            <div class="form-group">
                <label>Employee</label>
                <input type="text" class="form-control" value="{{ $employee->name }} ({{ $employee->employee_number }})" readonly>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="exit_date">Date of Exit <span class="text-danger">*</span></label>
                    <input type="date" name="exit_date" id="exit_date" class="form-control @error('exit_date') is-invalid @enderror" value="{{ old('exit_date', now()->format('Y-m-d')) }}" required>
                    @error('exit_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="exit_type">Type of Exit <span class="text-danger">*</span></label>
                    <select name="exit_type" id="exit_type" class="form-control @error('exit_type') is-invalid @enderror" required>
                        <option value="">Select a Type</option>
                        <option value="resigned" @if(old('exit_type') == 'resigned') selected @endif>Resigned</option>
                        <option value="terminated" @if(old('exit_type') == 'terminated') selected @endif>Terminated</option>
                        <option value="retired" @if(old('exit_type') == 'retired') selected @endif>Retired</option>
                        <option value="other" @if(old('exit_type') == 'other') selected @endif>Other</option>
                    </select>
                    @error('exit_type')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="exit_reason">Reason / Comments <span class="text-danger">*</span></label>
                <textarea name="exit_reason" id="exit_reason" class="form-control @error('exit_reason') is-invalid @enderror" rows="5" required>{{ old('exit_reason') }}</textarea>
                @error('exit_reason')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-danger">Confirm and Process Exit</button>
            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection