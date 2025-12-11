@extends('layouts.admin')
@section('title', 'Loans & Advances')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h3 class="card-title mb-0">Loans & Salary Advances</h3>
        <a href="{{ route('loans.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> New Request
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Type</th>
                    <th class="text-right">Total Amount</th>
                    <th class="text-right">Recovered</th>
                    <th class="text-right">Balance</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                    @php
                        $balance = $loan->total_amount - $loan->recovered_amount;
                        $progress = ($loan->total_amount > 0) ? ($loan->recovered_amount / $loan->total_amount) * 100 : 0;
                        // ✅ Logic: Only allow edit/delete if NO repayments have been made
                        $canEdit = $loan->recovered_amount <= 0;
                    @endphp
                    <tr>
                        <td>
                            {{ $loan->loan_date ? $loan->loan_date->format('d M, Y') : $loan->created_at->format('d M, Y') }}
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-dark">{{ $loan->employee->name }}</span>
                                <small class="text-muted">
                                    {{-- ✅ FIX: Show Employee ID instead of Designation --}}
                                    <i class="fas fa-id-badge mr-1"></i> {{ $loan->employee->employee_number ?? 'N/A' }}
                                </small>
                            </div>
                        </td>
                        <td>
                            @if($loan->type == 'advance')
                                <span class="badge badge-light border">Advance</span>
                            @else
                                <span class="badge badge-info">Loan</span>
                                <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">{{ $loan->installments }} Installments</small>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($loan->total_amount, 0) }}</td>
                        <td class="text-right text-success">
                            {{ number_format($loan->recovered_amount, 0) }}
                            <div class="progress progress-xs mt-1 bg-light" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                            </div>
                        </td>
                        <td class="text-right text-danger font-weight-bold">{{ number_format($balance, 0) }}</td>
                        <td class="text-center">
                            @if($loan->status == 'running')
                                <span class="badge badge-primary px-2 py-1">Running</span>
                            @elseif($loan->status == 'completed')
                                <span class="badge badge-success px-2 py-1">Completed</span>
                            @elseif($loan->status == 'cancelled')
                                <span class="badge badge-secondary px-2 py-1">Cancelled</span>
                            @else
                                <span class="badge badge-warning px-2 py-1">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm shadow-sm">
                                @if($canEdit)
                                    {{-- Edit Button --}}
                                    <a href="{{ route('loans.edit', $loan->id) }}" class="btn btn-light border" title="Edit Loan">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    
                                    {{-- Delete Button --}}
                                    <form action="{{ route('loans.destroy', $loan->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this loan record? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border text-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- Disabled Buttons --}}
                                    <button class="btn btn-light border disabled" title="Cannot edit (Repayments started)">
                                        <i class="fas fa-edit text-muted"></i>
                                    </button>
                                    <button class="btn btn-light border disabled" title="Cannot delete (Repayments started)">
                                        <i class="fas fa-trash-alt text-muted"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i>
                                <h5>No loans found</h5>
                                <p class="small mb-0">Use the "New Request" button to add one.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top-0 clearfix py-3">
        {{ $loans->links() }}
    </div>
</div>
@endsection