@extends('layouts.admin')
@section('title', "Salary Sheet for {$monthName}, {$year}")

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Salary Sheet for {{ $monthName }}, {{ $year }}</h3>
        <div class="card-tools">
            <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Back to List</a>
            <button onclick="window.print()" class="btn btn-primary no-print"><i class="fas fa-print"></i> Print Sheet</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Designation</th>
                    <th class="text-right">Gross Salary</th>
                    <th class="text-right">Deductions</th>
                    <th class="text-right">Income Tax</th>
                    <th class="text-right">Net Salary</th>
                    <th class="no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payslips as $payslip)
                    <tr>
                        <td>{{ $payslip->employee->name }}</td>
                        <td>{{ $payslip->employee->designation }}</td>
                        <td class="text-right">{{ number_format($payslip->gross_salary, 2) }}</td>
                        {{-- The payslip model does not have total_deductions, using the single 'deductions' field --}}
                        <td class="text-right">{{ number_format($payslip->deductions, 2) }}</td>
                        <td class="text-right">{{ number_format($payslip->income_tax, 2) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($payslip->net_salary, 2) }}</td>
                        <td class="no-print">
                            {{-- Corrected the route to use the new salaries.payslip route --}}
                            <a href="{{ route('salaries.payslip', $payslip->id) }}" target="_blank" class="btn btn-sm btn-info">View Payslip</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No payslips found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection