@extends('layouts.admin')
@section('title', 'Contributory Funds')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h3 class="card-title mb-0">Managed Funds</h3>
        {{-- ✅ NEW: Distribute Profit Button --}}
        <a href="{{ route('funds.profit.create') }}" class="btn btn-warning btn-sm mr-2">
            <i class="fas fa-chart-line mr-1"></i> Add Interest/Profit
        </a>
        <a href="{{ route('funds.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Setup New Fund
        </a>
        <a href="{{ route('funds.withdraw.create') }}" class="btn btn-danger btn-sm mr-2">
            <i class="fas fa-minus-circle mr-1"></i> Withdraw
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Fund Name</th>
                    <th>Linked Deduction</th>
                    <th>Employer Contribution Rule</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funds as $fund)
                    <tr>
                        <td class="font-weight-bold">{{ $fund->name }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ $fund->salaryComponent->name }}</span>
                        </td>
                        <td>
                            @if($fund->employer_contribution_type == 'match_employee')
                                <span class="text-success"><i class="fas fa-equals mr-1"></i> Matches Employee 100%</span>
                            @elseif($fund->employer_contribution_type == 'percentage_of_basic')
                                <span class="text-primary">{{ $fund->employer_contribution_value }}% of Basic Salary</span>
                            @else
                                <span class="text-info">Fixed: {{ number_format($fund->employer_contribution_value) }} PKR</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('funds.edit', $fund->id) }}" class="btn btn-xs btn-warning">Configure</a>
                            <form action="{{ route('funds.destroy', $fund->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this fund? History will remain but no new calculations will occur.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No funds configured yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection