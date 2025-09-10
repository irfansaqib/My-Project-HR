@extends('layouts.admin')
@section('title', 'Salary Sheet Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Salary Sheet for {{ $monthName }}</h3>
        </div>
        <div>
            <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('salaries.print', $salarySheet->id) }}" target="_blank" class="btn btn-warning"><i class="fas fa-print"></i> Print Sheet</a>
            <a href="{{ route('salaries.payslips.print-all', $salarySheet->id) }}" target="_blank" class="btn btn-info"><i class="fas fa-print"></i> Print All Payslips</a>
            
            <form action="{{ route('salaries.payslips.send-all', $salarySheet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to email payslips to all employees on this sheet?');">
                @csrf
                <button type="submit" class="btn btn-success"><i class="fas fa-envelope"></i> Email All Payslips</button>
            </form>
        </div>
    </div>
    <div class="card-body">
         @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm" style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">Sr.</th>
                        <th rowspan="2" class="text-center">Employee</th>
                        <th rowspan="2" class="text-center">Designation</th>
                        <th rowspan="2" class="text-center">Basic Salary</th>
                        @if($allowanceHeaders->count() > 0)
                            <th colspan="{{ $allowanceHeaders->count() }}" class="text-center">Allowances</th>
                        @endif
                        <th rowspan="2" class="text-center">Gross Salary</th>
                        @if($deductionHeaders->count() > 0 || true)
                            <th colspan="{{ $deductionHeaders->count() + 1 }}" class="text-center">Deductions</th>
                        @endif
                        <th rowspan="2" class="text-center">Net Salary</th>
                        <th rowspan="2" class="text-center">Actions</th>
                    </tr>
                    <tr>
                        @foreach($allowanceHeaders as $header)
                            <th class="text-center">{{ $header }}</th>
                        @endforeach
                        @foreach($deductionHeaders as $header)
                            <th class="text-center">{{ $header }}</th>
                        @endforeach
                        <th class="text-center">Income Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salarySheet->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item->employee->employee_number }} | {{ $item->employee->name }}</td>
                            <td>{{ $item->employee->designation }}</td>
                            <td class="text-right">{{ number_format($item->employee->basic_salary, 0) }}</td>
                            @foreach($allowanceHeaders as $header)
                                <td class="text-right">{{ number_format($item->allowances_breakdown[$header] ?? 0, 0) }}</td>
                            @endforeach
                            <td class="text-right">{{ number_format($item->gross_salary, 0) }}</td>
                            @foreach($deductionHeaders as $header)
                                <td class="text-right">{{ number_format($item->deductions_breakdown[$header] ?? 0, 0) }}</td>
                            @endforeach
                             <td class="text-right">{{ number_format($item->income_tax, 0) }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($item->net_salary, 0) }}</td>
                            <td class="text-center">
                                <a href="{{ route('salaries.payslip', $item->id) }}" class="btn btn-xs btn-info" target="_blank">View Payslip</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $allowanceHeaders->count() + $deductionHeaders->count() }}" class="text-center">No employee records found.</td>
                        </tr>
                    @endforelse
                </tbody>
                 <tfoot>
                    <tr>
                        <td colspan="3" class="font-weight-bold">Total</td>
                        <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum('employee.basic_salary'), 0) }}</td>
                        @foreach($allowanceHeaders as $header)
                            <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum(function($item) use ($header) { return $item->allowances_breakdown[$header] ?? 0; }), 0) }}</td>
                        @endforeach
                        <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum('gross_salary'), 0) }}</td>
                        @foreach($deductionHeaders as $header)
                             <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum(function($item) use ($header) { return $item->deductions_breakdown[$header] ?? 0; }), 0) }}</td>
                        @endforeach
                        <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum('income_tax'), 0) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($salarySheet->items->sum('net_salary'), 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection