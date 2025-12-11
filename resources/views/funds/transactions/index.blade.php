@extends('layouts.admin')
@section('title', 'Fund Transaction Ledger')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h3 class="card-title">Fund Transaction Ledger</h3>
    </div>
    
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" action="{{ route('funds.transactions.index') }}" class="row align-items-end mb-4">
            <div class="col-md-3 form-group">
                <label class="small text-muted">Employee</label>
                <select name="employee_id" class="form-control select2">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label class="small text-muted">Fund</label>
                <select name="fund_id" class="form-control">
                    <option value="">All Funds</option>
                    @foreach($funds as $fund)
                        <option value="{{ $fund->id }}" {{ request('fund_id') == $fund->id ? 'selected' : '' }}>{{ $fund->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label class="small text-muted">Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Fund</th>
                        <th>Type</th>
                        <th class="text-right">Amount (PKR)</th>
                        <th>Source/Description</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->transaction_date->format('d M, Y') }}</td>
                            <td>{{ $tx->employee->name }}</td>
                            <td>{{ $tx->fund->name }}</td>
                            <td>
                                @if($tx->type == 'profit_credit')
                                    <span class="badge badge-warning text-dark">Interest/Profit</span>
                                @elseif($tx->type == 'withdrawal')
                                    <span class="badge badge-danger">Withdrawal</span>
                                @else
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold">
                                @if(in_array($tx->type, ['withdrawal', 'cash_withdrawal']))
                                    <span class="text-danger">({{ number_format($tx->amount, 2) }})</span>
                                @else
                                    {{ number_format($tx->amount, 2) }}
                                @endif
                            </td>
                            <td class="text-muted small">{{ $tx->description }}</td>
                            <td class="text-center">
                                @if(!$tx->salary_sheet_item_id)
                                    <a href="{{ route('funds.transactions.edit', $tx->id) }}" class="btn btn-xs btn-warning" title="Edit Manual Transaction">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('funds.transactions.destroy', $tx->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this transaction?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                @else
                                    <button class="btn btn-xs btn-secondary" disabled title="Linked to Payroll Sheet">Locked</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No fund transactions recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() { if ($.fn.select2) { $('.select2').select2({ theme: 'bootstrap4' }); } });
</script>
@endpush