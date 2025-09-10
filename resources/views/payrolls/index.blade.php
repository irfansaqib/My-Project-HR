@extends('layouts.admin')
@section('title', 'Run Payroll')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Run Payroll</h3>
            <p class="card-text text-muted mb-0">This page lists pending salary sheets, grouped by the paying bank account assigned to each employee.</p>
        </div>
        <a href="{{ route('payrolls.history') }}" class="btn btn-secondary">View Payroll History</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @forelse ($pendingSheets as $sheet)
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        Salary Sheet for <strong>{{ $sheet->month->format('F, Y') }}</strong>
                    </h4>
                </div>
                <div class="card-body">
                    @forelse ($groupedItems[$sheet->month->format('Y-m-d')] as $bankId => $items)
                        @php
                            $firstItem = $items->first();
                            $bankName = $firstItem->employee->payingBankAccount->bank_name ?? 'Unassigned Bank Account';
                        @endphp
                        <h5 class="mb-3">
                            Pay From: <strong>{{ $bankName }}</strong>
                        </h5>
                        <table class="table table-sm table-bordered mb-4">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th class="text-right">Net Salary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->employee->name }}</td>
                                    <td class="text-right">PKR {{ number_format($item->net_salary, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td class="font-weight-bold">Sub-Total for this Bank</td>
                                    <td class="text-right font-weight-bold">PKR {{ number_format($items->sum('net_salary'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <form action="{{ route('payrolls.run-by-bank') }}" method="POST" onsubmit="return confirm('Are you sure you want to run payroll for this group? This action cannot be undone.');">
                            @csrf
                            <input type="hidden" name="salary_sheet_id" value="{{ $sheet->id }}">
                            <input type="hidden" name="business_bank_account_id" value="{{ $bankId }}">
                            <button type="submit" class="btn btn-sm btn-success mb-3">
                                Run Payroll for {{ $firstItem->employee->payingBankAccount->bank_name ?? 'Unassigned' }}
                            </button>
                        </form>
                    @empty
                        <p class="text-muted">All groups for this month have been paid.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="text-center">
                <p class="mb-0">There are no pending salary sheets to be processed.</p>
                <a href="{{ route('salaries.create') }}" class="btn btn-primary btn-sm mt-2">Generate a New Sheet</a>
            </div>
        @endforelse
    </div>
</div>
@endsection