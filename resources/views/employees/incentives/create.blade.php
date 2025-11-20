@extends('layouts.admin')
@section('title', 'Add Incentive for ' . $employee->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Incentive for: <strong>{{ $employee->name }}</strong></h3>
    </div>
    {{-- ✅ DEFINITIVE FIX: The route name in the form action is now corrected --}}
    <form action="{{ route('employees.incentives.store', $employee) }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="effective_date">Effective Date</label>
                    <input type="date" name="effective_date" id="effective_date" class="form-control" value="{{ old('effective_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="increment_amount">Amount (PKR)</label>
                    <input type="number" step="0.01" name="increment_amount" id="increment_amount" class="form-control" value="{{ old('increment_amount') }}" required>
                </div>
                 <div class="col-md-4 form-group">
                    <label for="type">Type</label>
                    <input type="text" name="type" id="type" class="form-control" value="bonus" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Incentive</button>
            <a href="{{ route('employees.incentives.index', $employee) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection