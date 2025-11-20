@extends('layouts.admin')
@section('title', 'Salary Revision Details')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Salary Revision Details</h3>
        <a href="{{ route('employees.revisions.index', $employee->id) }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">
        <h5><strong>Employee:</strong> {{ $employee->name }}</h5>
        <p><strong>Effective Date:</strong> {{ \Carbon\Carbon::parse($revision->effective_date)->format('d M, Y') }}</p>
        <p><strong>Status:</strong> 
            <span class="badge badge-{{ $revision->status === 'approved' ? 'success' : ($revision->status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst($revision->status) }}
            </span>
        </p>

        <hr>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Type</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Basic Salary</strong></td>
                    <td>Basic</td>
                    <td class="text-right">{{ number_format($revision->basic_salary, 2) }}</td>
                </tr>
                @foreach($structureData as $component)
                    <tr>
                        <td>{{ $component['name'] }}</td>
                        <td>{{ ucfirst($component['type']) }}</td>
                        <td class="text-right {{ $component['type'] == 'deduction' ? 'text-danger' : '' }}">
                            {{ $component['type'] == 'deduction' ? '(' : '' }}
                            {{ number_format($component['amount'], 2) }}
                            {{ $component['type'] == 'deduction' ? ')' : '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $allowances = collect($structureData)->where('type','allowance')->sum('amount');
            $deductions = collect($structureData)->where('type','deduction')->sum('amount');
            $gross = $revision->basic_salary + $allowances;
            $net = $gross - $deductions;
        @endphp

        <table class="table mt-3">
            <tr class="table-success">
                <td><strong>Gross Salary</strong></td>
                <td class="text-right">{{ number_format($gross, 2) }}</td>
            </tr>
            <tr class="table-dark text-white">
                <td><strong>Net Salary</strong></td>
                <td class="text-right">{{ number_format($net, 2) }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
