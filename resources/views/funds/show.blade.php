@extends('layouts.admin')
@section('title', $fund->name . ' - Details')

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 text-primary font-weight-bold"><i class="fas fa-piggy-bank mr-2"></i> {{ $fund->name }}</h4>
        <div>
            <a href="{{ route('funds.edit', $fund->id) }}" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i> Edit Settings</a>
            <a href="{{ route('funds.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Employee Share</span>
                    <span class="info-box-number">{{ number_format($totalEmployeeShare, 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Employer Share</span>
                    <span class="info-box-number">{{ number_format($totalEmployerShare, 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Profit / Interest</span>
                    <span class="info-box-number">{{ number_format($totalProfit, 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box shadow-sm bg-primary">
                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Fund Value</span>
                    <span class="info-box-number">{{ number_format($totalBalance, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuration Details --}}
    <div class="card collapsed-card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Configuration Details</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Linked Deduction</dt>
                <dd class="col-sm-9">{{ $fund->salaryComponent->name }}</dd>
                <dt class="col-sm-3">Employer Rule</dt>
                <dd class="col-sm-9">
                    @if($fund->employer_contribution_type == 'match_employee')
                        Match Employee (100%)
                    @elseif($fund->employer_contribution_type == 'percentage_of_basic')
                        {{ $fund->employer_contribution_value }}% of Basic Salary
                    @else
                        Fixed: {{ number_format($fund->employer_contribution_value) }}
                    @endif
                </dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $fund->description ?? '-' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Recent Activity Table --}}
    <div class="card shadow">
        <div class="card-header">
            <h3 class="card-title">Recent Activity (Last 20 Transactions)</h3>
            <div class="card-tools">
                <a href="{{ route('reports.funds', ['fund_id' => $fund->id]) }}" class="btn btn-xs btn-primary">View Full Report</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentContributions as $row)
                        <tr>
                            <td>{{ $row->transaction_date->format('d M, Y') }}</td>
                            <td class="font-weight-bold">{{ $row->employee->name }}</td>
                            <td>
                                @if($row->type == 'employee_share')
                                    <span class="badge badge-info">Employee Share</span>
                                @elseif($row->type == 'employer_share')
                                    <span class="badge badge-success">Employer Share</span>
                                @elseif($row->type == 'profit_credit')
                                    <span class="badge badge-warning text-dark">Interest</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($row->type) }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $row->description }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($row->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No transactions found yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection