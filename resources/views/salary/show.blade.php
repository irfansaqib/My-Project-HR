@extends('layouts.admin')
@section('title', "Salary Sheet for {$monthName}")

@push('styles')
<style>
    .table-responsive { overflow-x: auto; }
    .table th, .table td { white-space: nowrap; padding: 0.5rem; }
    .table thead th { vertical-align: middle; text-align: center; background-color: #e9ecef; }
    .table .employee-info { min-width: 180px; }
    .amount-col { text-align: right; }
    .header-logo { max-height: 60px; }
    /* THIS IS THE NEW STYLE FOR THE HEADER FONT SIZE */
    .sheet-header h3 { font-size: 1.75rem !important; font-weight: 600; }
    .sheet-header p { font-size: 1.1rem !important; }

    @media print {
        @page { size: landscape; }
        .no-print { display: none !important; }
        body { font-size: 9px; }
        .card { box-shadow: none !important; border: none !important; }
        .table th { background-color: #e9ecef !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header no-print">
        <h3 class="card-title">Salary Sheet for {{ $monthName }}</h3>
        <div class="card-tools">
            <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Back to List</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Sheet</button>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 sheet-header">
            <div style="flex: 1;">
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Business Logo" class="header-logo">
                @endif
            </div>
            <div class="text-center" style="flex: 2;">
                <h3 class="mb-0">{{ $business->legal_name ?? $business->name ?? 'Your Company' }}</h3>
                <p class="mb-0">Salary Sheet for the Month of {{ $monthName }}</p>
            </div>
            <div style="flex: 1;">
                {{-- This is an empty spacer to ensure the middle div is perfectly centered --}}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th rowspan="2">Sr. No.</th>
                        <th rowspan="2" class="employee-info">Employee Name</th>
                        <th rowspan="2">CNIC No.</th>
                        <th rowspan="2">Designation</th>
                        <th rowspan="2">Basic Salary</th>
                        <th colspan="{{ $allowanceHeaders->count() }}">Allowances</th>
                        <th rowspan="2">Gross Salary</th>
                        <th colspan="{{ $deductionHeaders->count() + 1 }}">Deductions</th>
                        <th rowspan="2">Net Salary</th>
                        <th rowspan="2">Bank Account</th>
                        <th rowspan="2">Signature</th>
                        {{-- ADDED THIS HEADER --}}
                        <th rowspan="2" class="no-print">Actions</th>
                    </tr>
                    <tr>
                        @foreach($allowanceHeaders as $header)
                            <th>{{ $header }}</th>
                        @endforeach

                        <th>Tax</th>
                        @foreach($deductionHeaders as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salarySheet->items as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="employee-info">{{ $item->employee->name }}</td>
                            <td>{{ $item->employee->cnic }}</td>
                            <td>{{ $item->employee->designation->name ?? '' }}</td>
                            <td class="amount-col">{{ number_format($item->employee->basic_salary, 2) }}</td>
                            
                            @foreach($allowanceHeaders as $header)
                                <td class="amount-col">{{ number_format($item->allowances[$header] ?? 0, 2) }}</td>
                            @endforeach

                            <td class="amount-col font-weight-bold">{{ number_format($item->gross_salary, 2) }}</td>
                            
                            <td class="amount-col">{{ number_format($item->income_tax, 2) }}</td>
                            @foreach($deductionHeaders as $header)
                                <td class="amount-col">{{ number_format($item->deductions[$header] ?? 0, 2) }}</td>
                            @endforeach

                            <td class="amount-col font-weight-bold">{{ number_format($item->net_salary, 2) }}</td>
                            <td>{{ $item->employee->bank_account_number }}</td>
                            <td></td>
                            {{-- ADDED THIS CELL WITH THE PAYSLIP LINK --}}
                            <td class="no-print">
                                <a href="{{ route('salaries.payslip', $item->id) }}" class="btn btn-sm btn-info" target="_blank">View Payslip</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 11 + $allowanceHeaders->count() + $deductionHeaders->count() }}" class="text-center">No employees found for this salary sheet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection