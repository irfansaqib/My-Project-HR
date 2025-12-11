@extends('layouts.admin')
@section('title', 'Tax Deduction Report')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tax Deductions Report</h3>
    </div>
    <div class="card-body bg-light border-bottom">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label>From Month</label>
                <input type="month" name="from_month" value="{{ $fromDate->format('Y-m') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>To Month</label>
                <input type="month" name="to_month" value="{{ $toDate->format('Y-m') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Employee</label>
                <select name="employee_id" class="form-control select2">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp) 
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    {{-- ✅ NEW: Export Button --}}
                    <button type="submit" name="export" value="excel" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</button>
                </div>
            </div>
        </form>

        {{-- Bulk Actions (Certificates) --}}
        <div class="mt-3 text-right border-top pt-3">
            @php 
                $fyStart = $fromDate->month >= 7 ? $fromDate->year : $fromDate->year - 1;
                $fyString = $fyStart . '-' . ($fyStart + 1);
            @endphp

            <span class="text-muted mr-2 small">Actions for FY {{ $fyString }}:</span>

            <form action="{{ route('salaries.tax.print-all') }}" method="POST" target="_blank" class="d-inline mr-2">
                @csrf
                <input type="hidden" name="fy" value="{{ $fyString }}">
                @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                <button class="btn btn-outline-dark btn-sm"><i class="fas fa-print mr-1"></i> Print Certificates</button>
            </form>

            <form action="{{ route('salaries.tax.email') }}" method="POST" class="d-inline" onsubmit="return confirm('Email tax certificates to ALL selected employees?');">
                @csrf
                <input type="hidden" name="fy" value="{{ $fyString }}">
                @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                <button class="btn btn-dark btn-sm"><i class="fas fa-envelope mr-1"></i> Email Certificates</button>
            </form>
        </div>
    </div>
    
    <div class="card-body table-responsive p-0">
        <table class="table table-striped text-sm">
            <thead class="bg-dark text-white">
                <tr><th>Month</th><th>Employee</th><th>CNIC</th><th class="text-right">Gross Salary</th><th class="text-right">Tax Deducted</th></tr>
            </thead>
            <tbody>
                @foreach($records as $row)
                <tr>
                    <td>{{ $row->salarySheet->month->format('M, Y') }}</td>
                    <td>{{ $row->employee->name }}</td>
                    <td>{{ $row->employee->cnic }}</td>
                    <td class="text-right">{{ number_format($row->gross_salary) }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($row->income_tax) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">{{ number_format($totalGross) }}</td>
                    <td class="text-right">{{ number_format($totalTax) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection