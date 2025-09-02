@extends('layouts.admin')
@section('title', 'Tax Rates')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Tax Rates by Year</h3>
        <a href="{{ route('tax-rates.create') }}" class="btn btn-primary float-right">Add New Tax Year Rates</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tax Year</th>
                    <th>Effective Date Range</th>
                    <th>Slabs Defined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taxRates as $rate)
                    <tr>
                        <td><strong>{{ $rate->tax_year }}</strong></td>
                        <td>{{ $rate->effective_from_date->format('d M, Y') }} to {{ $rate->effective_to_date ? $rate->effective_to_date->format('d M, Y') : 'Present' }}</td>
                        <td>{{ count($rate->slabs) }}</td>
                        <td>
                            <a href="{{ route('tax-rates.edit', $rate) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('tax-rates.destroy', $rate) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No tax rates found. Please add a set for a tax year.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection