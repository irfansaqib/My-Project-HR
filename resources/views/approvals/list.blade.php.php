@extends('layouts.admin')
@section('title', 'Pending Salary Revisions')

@push('styles')
<style>
    .status-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.6em;
        border-radius: 0.35rem;
        text-transform: capitalize;
    }
    .status-pending { background-color: #ffc107; color: #000; }
    .status-approved { background-color: #28a745; color: #fff; }
    .status-rejected { background-color: #dc3545; color: #fff; }
    .table th, .table td { vertical-align: middle !important; }
    .modal-lg { max-width: 800px; }
    .salary-diff-positive { color: #28a745; font-weight: 600; }
    .salary-diff-negative { color: #dc3545; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Pending Salary Revisions for Approval</h3>
        <span class="badge badge-warning">Pending: {{ $revisions->count() }}</span>
    </div>

    <div class="card-body">
        @if($revisions->isEmpty())
            <div class="alert alert-info text-center mb-0">
                There are no pending salary revisions to review.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-sm">
                <thead class="thead-light">
                    <tr class="text-center">
                        <th>Employee</th>
                        <th>Designation</th>
                        <th>Effective Date</th>
                        <th>Basic Salary</th>
                        <th>Gross Salary</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revisions as $rev)
                        @php
                            $components = collect($rev->salary_components ?? []);
                            $allowances = $components->where('type', 'allowance')->sum('amount');
                            $deductions = $components->where('type', 'deduction')->sum('amount');
                            $gross = $rev->basic_salary + $allowances;
                            $net = $gross - $deductions;

                            // Fetch current salary from Employee
                            $empGross = $rev->employee->gross_salary ?? 0;
                            $empNet = $rev->employee->net_salary ?? 0;
                            $empBasic = $rev->employee->basic_salary ?? 0;
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $rev->employee->name }}</strong><br>
                                <small class="text-muted">{{ $rev->employee->email ?? '' }}</small>
                            </td>
                            <td class="text-center">{{ $rev->employee->designation->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($rev->effective_date)->format('d M, Y') }}</td>
                            <td class="text-right">{{ number_format($rev->basic_salary, 2) }}</td>
                            <td class="text-right">{{ number_format($gross, 2) }}</td>
                            <td class="text-right">{{ number_format($net, 2) }}</td>
                            <td class="text-center">
                                <span class="status-badge status-{{ strtolower($rev->status) }}">
                                    {{ ucfirst($rev->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#summaryModal{{ $rev->id }}">
                                    <i class="fas fa-chart-line"></i> Summary
                                </button>
                                <a href="{{ route('salary.revisions.approve.view', $rev->id) }}" 
                                   class="btn btn-sm btn-primary mt-1">
                                   <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>

                        <!-- 🟩 Modal: View Change Summary -->
                        <div class="modal fade" id="summaryModal{{ $rev->id }}" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel{{ $rev->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="summaryModalLabel{{ $rev->id }}">
                                            Salary Change Summary – {{ $rev->employee->name }}
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-bordered table-sm text-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Component</th>
                                                    <th class="text-right">Current</th>
                                                    <th class="text-right">Proposed</th>
                                                    <th class="text-right">Change</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Basic Salary</strong></td>
                                                    <td class="text-right">{{ number_format($empBasic, 2) }}</td>
                                                    <td class="text-right">{{ number_format($rev->basic_salary, 2) }}</td>
                                                    @php $diffBasic = $rev->basic_salary - $empBasic; @endphp
                                                    <td class="text-right {{ $diffBasic >= 0 ? 'salary-diff-positive' : 'salary-diff-negative' }}">
                                                        {{ $diffBasic >= 0 ? '+' : '' }}{{ number_format($diffBasic, 2) }}
                                                    </td>
                                                </tr>
                                                @foreach($components as $comp)
                                                    @php
                                                        $currentComp = $rev->employee->salaryComponents->firstWhere('name', $comp['name']);
                                                        $currAmt = $currentComp ? $currentComp->pivot->amount : 0;
                                                        $diff = $comp['amount'] - $currAmt;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $comp['name'] }}</td>
                                                        <td class="text-right">{{ $comp['type'] == 'deduction' ? '(' . number_format($currAmt, 2) . ')' : number_format($currAmt, 2) }}</td>
                                                        <td class="text-right">{{ $comp['type'] == 'deduction' ? '(' . number_format($comp['amount'], 2) . ')' : number_format($comp['amount'], 2) }}</td>
                                                        <td class="text-right {{ $diff >= 0 ? 'salary-diff-positive' : 'salary-diff-negative' }}">
                                                            {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-success font-weight-bold">
                                                    <td>Gross Salary</td>
                                                    <td class="text-right">{{ number_format($empGross, 2) }}</td>
                                                    <td class="text-right">{{ number_format($gross, 2) }}</td>
                                                    @php $grossDiff = $gross - $empGross; @endphp
                                                    <td class="text-right {{ $grossDiff >= 0 ? 'salary-diff-positive' : 'salary-diff-negative' }}">
                                                        {{ $grossDiff >= 0 ? '+' : '' }}{{ number_format($grossDiff, 2) }}
                                                    </td>
                                                </tr>
                                                <tr class="table-dark font-weight-bold">
                                                    <td>Net Payable</td>
                                                    <td class="text-right">{{ number_format($empNet, 2) }}</td>
                                                    <td class="text-right">{{ number_format($net, 2) }}</td>
                                                    @php $netDiff = $net - $empNet; @endphp
                                                    <td class="text-right {{ $netDiff >= 0 ? 'salary-diff-positive' : 'salary-diff-negative' }}">
                                                        {{ $netDiff >= 0 ? '+' : '' }}{{ number_format($netDiff, 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ route('salary.revisions.approve.view', $rev->id) }}" class="btn btn-primary">
                                            <i class="fas fa-check-circle"></i> Review Full Revision
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Modal -->
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
