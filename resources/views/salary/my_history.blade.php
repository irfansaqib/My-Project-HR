@extends('layouts.admin')
@section('title', 'My Salary History')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> My Payslips</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Earnings</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $slip)
                <tr>
                    <td>
                        <strong>{{ $slip->salarySheet->month->format('F, Y') }}</strong>
                    </td>
                    <td class="text-success">
                        {{ number_format($slip->gross_salary) }}
                    </td>
                    <td class="text-danger">
                        {{ number_format($slip->deductions + $slip->income_tax) }}
                    </td>
                    <td class="font-weight-bold text-primary">
                        {{ number_format($slip->payable_amount) }}
                    </td>
                    <td>
                        @if($slip->payment_status == 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($slip->payment_status == 'held')
                            <span class="badge badge-warning">Held</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($slip->payment_status) }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('salaries.payslip', $slip->id) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-download"></i> Payslip
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No salary records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $payslips->links() }}
    </div>
</div>
@endsection