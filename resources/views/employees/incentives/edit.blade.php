@extends('layouts.admin')
@section('title', 'Edit Bonus')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Bonus for: <strong>{{ $employee->name }}</strong></h3>
    </div>
    <form action="{{ route('employees.incentives.update', [$employee, $incentive]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" name="effective_date" id="effective_date" class="form-control" value="{{ old('effective_date', $incentive->effective_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="increment_amount">Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="increment_amount" id="increment_amount" class="form-control" value="{{ old('increment_amount', $incentive->increment_amount) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description <span class="text-danger">*</span></label>
                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description', $incentive->description) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Bonus</button>
            <a href="{{ route('employees.incentives.index', $employee) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection