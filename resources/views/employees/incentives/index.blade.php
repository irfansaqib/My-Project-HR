@extends('layouts.admin')
@section('title', 'Bonus History for ' . $employee->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bonus History for: <strong>{{ $employee->name }}</strong></h3>
        <a href="{{ route('employees.incentives.create', $employee) }}" class="btn btn-primary float-right">Add New Bonus</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Effective Date</th>
                    <th>Description</th>
                    <th class="text-right">Amount (PKR)</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incentives as $incentive)
                    <tr>
                        <td>{{ $incentive->effective_date->format('d M, Y') }}</td>
                        <td>{{ $incentive->description }}</td>
                        <td class="text-right">{{ number_format($incentive->increment_amount, 2) }}</td>
                        {{-- ✅ NEW: Added Edit button --}}
                        <td>
                            <a href="{{ route('employees.incentives.edit', [$employee, $incentive]) }}" class="btn btn-xs btn-warning">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No bonuses have been recorded for this employee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Back to Employee Profile</a>
        </div>
    </div>
</div>
@endsection