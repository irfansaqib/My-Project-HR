@extends('layouts.admin')
@section('title', 'View Tax Rates for ' . $taxRate->tax_year)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tax Rate Details for Tax Year: <strong>{{ $taxRate->tax_year }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('tax-rates.edit', $taxRate) }}" class="btn btn-primary">Edit These Rates</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Effective Date Range:</strong><br>
                {{ $taxRate->effective_from_date->format('d M, Y') }} to {{ $taxRate->effective_to_date ? $taxRate->effective_to_date->format('d M, Y') : 'Present' }}
            </div>
            <div class="col-md-6">
                <strong>Surcharge on High Earners:</strong><br>
                {{ $taxRate->surcharge_rate_percentage ?? 0 }}% on income above {{ number_format($taxRate->surcharge_threshold ?? 0, 2) }}
            </div>
        </div>

        <h4 class="mt-4">Tax Slabs</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Income From (PKR)</th>
                    <th>Income To (PKR)</th>
                    <th>Fixed Tax (PKR)</th>
                    <th>Tax Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($taxRate->slabs as $slab)
                    <tr>
                        <td>{{ number_format($slab['income_from'], 2) }}</td>
                        <td>{{ $slab['income_to'] ? number_format($slab['income_to'], 2) : 'And Above' }}</td>
                        <td>{{ number_format($slab['fixed_tax_amount'], 2) }}</td>
                        <td>{{ $slab['tax_rate_percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection