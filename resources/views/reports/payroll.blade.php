@extends('layouts.admin')
@section('title', 'Payroll Cost Analysis')

@section('content')

{{-- COST SUMMARY CARDS --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Gross Pay</span>
                <span class="info-box-number">{{ number_format($totalGross) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Tax Deducted</span>
                <span class="info-box-number">{{ number_format($totalTax) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-cut"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Other Deductions</span>
                <span class="info-box-number">{{ number_format($totalDeductions) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Net Disbursed</span>
                <span class="info-box-number">{{ number_format($totalNet) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- FILTERS --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Date Range Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.payroll') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label>From Month</label>
                    <input type="month" name="from_month" class="form-control" value="{{ $fromMonth->format('Y-m') }}">
                </div>
                <div class="col-md-3">
                    <label>To Month</label>
                    <input type="month" name="to_month" class="form-control" value="{{ $toMonth->format('Y-m') }}">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Generate Analysis</button>
                    <button type="submit" name="export" value="excel" class="btn btn-success"><i class="fas fa-file-excel mr-1"></i> Export Summary</button>
                    <a href="{{ route('reports.payroll') }}" class="btn btn-secondary ml-2">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- DATA TABLE --}}
<div class="card shadow-sm">
    <div class="card-header bg-white border-0">
        <h3 class="card-title font-weight-bold">Monthly Cost Breakdown</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Month</th>
                    <th class="text-center">Total Employees</th> 
                    <th class="text-right">Gross Pay</th>
                    <th class="text-right text-danger">Tax</th>
                    <th class="text-right text-warning">Deductions</th>
                    <th class="text-right text-success font-weight-bold">Net Pay</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sheets as $sheet)
                    <tr>
                        <td class="font-weight-bold">{{ $sheet->month->format('F, Y') }}</td>
                        
                        <td class="text-center">
                            <span class="badge badge-info">{{ $sheet->items_count }}</span>
                        </td>

                        <td class="text-right">{{ number_format($sheet->total_gross_salary) }}</td>
                        <td class="text-right text-danger">{{ number_format($sheet->total_tax) }}</td>
                        <td class="text-right text-warning">{{ number_format($sheet->total_deductions) }}</td>
                        <td class="text-right text-success font-weight-bold">{{ number_format($sheet->total_net_salary) }}</td>
                        <td class="text-center">
                            {{-- Link back to the Operational View for details --}}
                            <a href="{{ route('salaries.show', $sheet->id) }}" class="btn btn-xs btn-outline-primary" target="_blank">
                                <i class="fas fa-list-alt mr-1"></i> View Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-gray-300"></i><br>
                            No finalized salary sheets found for this period.<br>
                            <small>Make sure you have finalized the salary sheet for this month.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection