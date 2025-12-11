@extends('layouts.admin')
@section('title', 'Edit Fund Transaction')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Edit Fund Transaction ({{ ucfirst($transaction->type) }})</h3>
    </div>
    <form action="{{ route('funds.transactions.update', $transaction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="alert alert-info">
                This transaction is not linked to a payroll sheet and can be manually modified.
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Employee</label>
                    <input type="text" class="form-control" value="{{ $transaction->employee->name }}" readonly>
                    <input type="hidden" name="employee_id" value="{{ $transaction->employee_id }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Fund</label>
                    <input type="text" class="form-control" value="{{ $transaction->fund->name }}" readonly>
                    <input type="hidden" name="fund_id" value="{{ $transaction->fund_id }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" class="form-control" required min="0.01" value="{{ old('amount', $transaction->amount) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" class="form-control" required value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $transaction->description) }}">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Transaction</button>
            <a href="{{ route('funds.transactions.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection