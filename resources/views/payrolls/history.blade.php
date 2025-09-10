@extends('layouts.admin')
@section('title', 'Payroll History')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Payroll History</h3>
            <p class="card-text text-muted mb-0">List of all completed payroll runs.</p>
        </div>
        <a href="{{ route('payrolls.index') }}" class="btn btn-primary">Run Payroll</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Salary Month</th>
                        <th>Notes</th>
                        <th class="text-right">Amount Paid (PKR)</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr>
                        <td>{{ $payroll->payment_date->format('d M, Y H:i A') }}</td>
                        <td>{{ $payroll->salarySheet->month->format('F, Y') }}</td>
                        <td>{{ $payroll->notes }}</td>
                        <td class="text-right">{{ number_format($payroll->total_amount, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('payrolls.download', $payroll->id) }}" class="btn btn-sm btn-info" title="Download Bank File">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No payroll history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $payrolls->links() }}
        </div>
    </div>
</div>
@endsection