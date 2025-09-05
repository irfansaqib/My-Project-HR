@extends('layouts.admin')
@section('title', 'Run Payroll')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Run Payroll</h3>
        <p class="card-text text-muted">This page lists pending salary sheets, grouped by the paying bank account assigned to each employee.</p>
    </div>
    <div class="card-body">
        @forelse ($pendingSheets as $sheet)
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        Salary Sheet for <strong>{{ \Carbon\Carbon::parse($sheet->month)->format('F, Y') }}</strong>
                    </h4>
                </div>
                <div class="card-body">
                    @foreach ($groupedItems[$sheet->month] as $bankName => $items)
                        <h5 class="mb-3">
                            Pay From: <strong>{{ $bankName ?: 'Unassigned Bank Account' }}</strong>
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
                        {{-- This "Run Payroll" button would need further logic to process only this group --}}
                        <button class="btn btn-sm btn-success mb-3" disabled>Run Payroll for {{ $bankName ?: 'Unassigned' }}</button>
                    @endforeach
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