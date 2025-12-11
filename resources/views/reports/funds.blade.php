@extends('layouts.admin')

@section('title', 'Contributory Funds Report')

@section('content')
<div class="container-fluid">
    
    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3 bg-light rounded">
            <form method="GET" action="{{ route('reports.funds') }}" class="row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label class="small text-muted font-weight-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" 
                           value="{{ request('from_date', $fromDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3 form-group mb-2">
                    <label class="small text-muted font-weight-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" 
                           value="{{ request('to_date', $toDate->format('Y-m-d')) }}">
                </div>
                
                <div class="col-md-2 form-group mb-2">
                    <label class="small text-muted font-weight-bold">Employee</label>
                    <select name="employee_id" class="form-control form-control-sm select2">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="small text-muted font-weight-bold">Fund</label>
                    <select name="fund_id" class="form-control form-control-sm">
                        <option value="">All Funds</option>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}" {{ request('fund_id') == $fund->id ? 'selected' : '' }}>
                                {{ $fund->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards (Updated Layout) --}}
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Employee Share</span>
                    <span class="info-box-number">{{ number_format($totalEmployeeShare, 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Employer Share</span>
                    <span class="info-box-number">{{ number_format($totalEmployerShare, 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Profit</span>
                    <span class="info-box-number">{{ number_format($totalProfit, 0) }}</span>
                </div>
            </div>
        </div>
        
        {{-- ✅ NEW: Withdrawal Card --}}
        <div class="col-lg-2 col-md-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-minus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-danger">Withdrawals</span>
                    <span class="info-box-number text-danger">({{ number_format($totalWithdrawals, 0) }})</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-8 col-12">
            <div class="info-box shadow-sm bg-light border">
                <div class="info-box-content text-center py-2">
                    <span class="info-box-text text-primary font-weight-bold text-uppercase" style="font-size: 1rem;">Total Fund Value</span>
                    <span class="info-box-number text-dark" style="font-size: 1.8rem;">{{ number_format($totalFundValue, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Detailed Transaction Log</h3>
            <form method="GET" action="{{ route('reports.funds') }}" target="_blank">
                @foreach(request()->all() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="hidden" name="export" value="excel">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Export to Excel</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Fund</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contributions as $row)
                            <tr>
                                <td>{{ $row->transaction_date->format('d M, Y') }}</td>
                                <td class="font-weight-bold">{{ $row->employee->name }}</td>
                                <td>{{ $row->fund->name }}</td>
                                <td>
                                    @if($row->type == 'employee_share')
                                        <span class="badge badge-info">Employee Share</span>
                                    @elseif($row->type == 'employer_share')
                                        <span class="badge badge-success">Employer Share</span>
                                    @elseif($row->type == 'profit_credit')
                                        <span class="badge badge-warning text-dark">Interest</span>
                                    @elseif($row->type == 'withdrawal')
                                        {{-- ✅ Withdrawal Badge --}}
                                        <span class="badge badge-secondary">Withdrawal</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $row->type)) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $row->description }}</td>
                                
                                {{-- ✅ Conditional Formatting for Amounts --}}
                                <td class="text-right font-weight-bold {{ $row->type == 'withdrawal' ? 'text-danger' : '' }}">
                                    @if($row->type == 'withdrawal')
                                        ({{ number_format($row->amount, 2) }})
                                    @else
                                        {{ number_format($row->amount, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No fund transactions found for the selected criteria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4' });
        }
    });
</script>
@endpush