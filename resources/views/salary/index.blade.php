@extends('layouts.admin')
@section('title', 'Salary Sheets')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Generated Salary Sheets</h3>
        <div class="card-tools">
            <a href="{{ route('salaries.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Generate New Sheet
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Status</th>
                    <th>Employees</th>
                    <th class="text-right">Total Payable</th>
                    <th class="text-center" style="width: 350px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salarySheets as $sheet)
                <tr>
                    <td class="align-middle">
                        <strong>{{ $sheet->month->format('F, Y') }}</strong><br>
                        <small class="text-muted">Generated: {{ $sheet->created_at->format('d M, Y') }}</small>
                    </td>
                    
                    <td class="align-middle">
                        @if($sheet->status === 'finalized')
                            <span class="badge badge-success"><i class="fas fa-lock"></i> Finalized</span>
                        @else
                            <span class="badge badge-warning">Draft Mode</span>
                        @endif
                    </td>

                    <td class="align-middle">{{ $sheet->items_count }}</td>
                    
                    <td class="align-middle text-right font-weight-bold">
                        {{ number_format($sheet->items->sum('payable_amount')) }}
                    </td>

                    <td class="align-middle text-center">
                        <div class="btn-group">
                            {{-- 1. View --}}
                            <a href="{{ route('salaries.show', $sheet->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </a>

                            @if($sheet->status === 'finalized')
                                {{-- 2. Print Summary --}}
                                <a href="{{ route('salaries.print', $sheet->id) }}" target="_blank" class="btn btn-sm btn-default" title="Print Summary Sheet">
                                    <i class="fas fa-print"></i>
                                </a>

                                {{-- 3. Payslips --}}
                                <a href="{{ route('salaries.payslips.print-all', $sheet->id) }}" target="_blank" class="btn btn-sm btn-info" title="Print All Payslips">
                                    <i class="fas fa-file-invoice"></i>
                                </a>

                                {{-- 4. Bank Excel (Combined) --}}
                                <a href="{{ route('salaries.export-bank', $sheet->id) }}" class="btn btn-sm btn-success" title="Download Bank Excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>

                                {{-- 5. Email --}}
                                <form action="{{ route('salaries.payslips.send-all', $sheet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Send Payslips via Email to ALL employees?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-dark" title="Email All Payslips">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- 6. Delete --}}
                            <form action="{{ route('salaries.destroy', $sheet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will reverse all payments.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Sheet">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No salary sheets generated yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $salarySheets->links() }}
    </div>
</div>
@endsection