@extends('layouts.admin')

@section('title', 'Loan & Advance Report')

@section('content')
<div class="container-fluid">
    
    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3 bg-light rounded">
            <form method="GET" action="{{ route('reports.loans') }}" class="row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label class="small text-muted font-weight-bold">As At Date</label>
                    <input type="date" name="as_at_date" class="form-control form-control-sm" 
                           value="{{ request('as_at_date', now()->format('Y-m-d')) }}">
                </div>
                
                <div class="col-md-3 form-group mb-2">
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
                    <label class="small text-muted font-weight-bold">Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <option value="advance" {{ request('type') == 'advance' ? 'selected' : '' }}>Salary Advance</option>
                        <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Standard Loan</option>
                        <option value="fund_loan" {{ request('type') == 'fund_loan' ? 'selected' : '' }}>Fund Loan</option>
                    </select>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="small text-muted font-weight-bold">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running / Unpaid</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Disbursed</span>
                    <span class="info-box-number">{{ number_format($reportData->sum('total_amount'), 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Recovered</span>
                    <span class="info-box-number">{{ number_format($reportData->sum('recovered'), 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Outstanding</span>
                    <span class="info-box-number">{{ number_format($reportData->sum('balance'), 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Loan & Advance Summary</h3>
            <form method="GET" action="{{ route('reports.loans') }}" target="_blank">
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
                            <th>Type</th>
                            <th class="text-right">Total Amount</th>
                            <th class="text-right">Recovered</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td class="font-weight-bold text-primary">{{ $row->employee_name }}</td>
                                <td>
                                    @if($row->type == 'Advance')
                                        <span class="badge badge-light border text-dark">Advance</span>
                                    @elseif($row->type == 'Fund Loan')
                                        {{-- Fund Loan (Distinguished by color and icon) --}}
                                        <span class="badge bg-purple text-white" style="background-color: #6f42c1;" title="Linked to: {{ $row->fund_name }}">
                                            <i class="fas fa-piggy-bank mr-1"></i> Fund Loan
                                        </span>
                                    @else
                                        <span class="badge badge-info">Standard Loan</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($row->total_amount, 0) }}</td>
                                <td class="text-right text-success">{{ number_format($row->recovered, 0) }}</td>
                                <td class="text-right text-danger font-weight-bold">{{ number_format($row->balance, 0) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $row->status == 'Completed' ? 'success' : 'warning' }}">{{ $row->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No records found for the selected criteria.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="3" class="text-right">Total:</td>
                            <td class="text-right">{{ number_format($reportData->sum('total_amount'), 0) }}</td>
                            <td class="text-right text-success">{{ number_format($reportData->sum('recovered'), 0) }}</td>
                            <td class="text-right text-danger">{{ number_format($reportData->sum('balance'), 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
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