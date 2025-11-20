@extends('layouts.admin')

@section('title', 'Salary Sheet Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Salary Sheet for {{ $monthName }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Back to List</a>
                        <a href="{{ route('salaries.print', $salarySheet) }}" target="_blank" class="btn btn-warning"><i class="fas fa-print"></i> Print Sheet</a>
                        <a href="{{ route('salaries.payslips.print-all', $salarySheet) }}" target="_blank" class="btn btn-info"><i class="fas fa-print"></i> Print All Payslips</a>
                        <form action="{{ route('salaries.payslips.send-all', $salarySheet) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to email all payslips? This may take some time.');">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fas fa-envelope"></i> Email All Payslips</button>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Employee</th>
                                <th>Designation</th>
                                <th class="text-right">Basic Salary</th>
                                @foreach($allowanceHeaders as $header)
                                    <th class="text-right">{{ $header }}</th>
                                @endforeach
                                <th class="text-right">Bonus</th>
                                <th class="text-right">Gross Salary</th>
                                @foreach($deductionHeaders as $header)
                                    <th class="text-right">{{ $header }}</th>
                                @endforeach
                                <th class="text-right">Income Tax</th>
                                <th class="text-right">Net Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salarySheet->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->employee->name }}</td>
                                {{-- ✅ THE FINAL FIX: Access the 'designation' attribute directly --}}
                                <td>{{ $item->employee->designation ?? 'N/A' }}</td>
                                <td class="text-right">{{ number_format($item->employee->basic_salary, 2) }}</td>
                                @foreach($allowanceHeaders as $header)
                                    <td class="text-right">{{ number_format($item->allowances_breakdown[$header] ?? 0, 2) }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($item->bonus, 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->gross_salary, 2) }}</td>
                                @foreach($deductionHeaders as $header)
                                    <td class="text-right">{{ number_format($item->deductions_breakdown[$header] ?? 0, 2) }}</td>
                                @endforeach
                                <td class="text-right text-danger">({{ number_format($item->income_tax, 2) }})</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->net_salary, 2) }}</td>
                                <td>
                                    <a href="{{ route('salaries.payslip', $item->id) }}" class="btn btn-sm btn-primary" target="_blank">View Payslip</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3">Total</td>
                                <td class="text-right">{{ number_format($salarySheet->items->sum('employee.basic_salary'), 2) }}</td>
                                @foreach($allowanceHeaders as $header)
                                    <td class="text-right">{{ number_format($salarySheet->items->sum(fn($item) => $item->allowances_breakdown[$header] ?? 0), 2) }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($salarySheet->items->sum('bonus'), 2) }}</td>
                                <td class="text-right">{{ number_format($salarySheet->items->sum('gross_salary'), 2) }}</td>
                                @foreach($deductionHeaders as $header)
                                    <td class="text-right">{{ number_format($salarySheet->items->sum(fn($item) => $item->deductions_breakdown[$header] ?? 0), 2) }}</td>
                                @endforeach
                                <td class="text-right">({{ number_format($salarySheet->items->sum('income_tax'), 2) }})</td>
                                <td class="text-right">{{ number_format($salarySheet->items->sum('net_salary'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

