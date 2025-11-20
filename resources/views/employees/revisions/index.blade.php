@extends('layouts.admin')
@section('title', 'Salary Revisions')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <div>
            <h3 class="card-title mb-1">Salary Revisions</h3>
            <small class="text-muted d-block">
                Employee: <strong>{{ $employee->name }}</strong>
            </small>
            <small class="text-muted d-block">
                Designation / Department:
                <strong>{{ $employee->designationRelation->name ?? 'N/A' }} / {{ $employee->departmentRelation->name ?? 'N/A' }}</strong>
            </small>
        </div>
        <div>
            <a href="{{ route('employees.revisions.create', $employee->id) }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> New Revision
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($revisions->isEmpty())
            <p class="text-muted mb-0">No salary revisions found for this employee.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>Effective Date</th>
                            <th>Basic Salary (Rs)</th>
                            <th>Gross Salary (Rs)</th>
                            <th>Net Salary (Rs)</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Approved At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach($revisions as $revision)
                            @php
                                // Decode salary components and compute totals
                                $components = is_string($revision->salary_components)
                                    ? json_decode($revision->salary_components, true)
                                    : ($revision->salary_components ?? []);

                                $allowances = collect($components)->where('type', 'allowance')->sum('amount');
                                $deductions = collect($components)->where('type', 'deduction')->sum('amount');
                                $gross = (float) $revision->basic_salary + $allowances;
                                $net = $gross - $deductions;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($revision->effective_date)->format('d M, Y') }}</td>
                                <td>{{ number_format($revision->basic_salary, 2) }}</td>
                                <td>{{ number_format($gross, 2) }}</td>
                                <td>{{ number_format($net, 2) }}</td>

                                <td>
                                    @if($revision->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($revision->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($revision->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($revision->status) }}</span>
                                    @endif
                                </td>

                                <td>{{ $revision->approved_by ? ($revision->approver->name ?? 'System') : '-' }}</td>
                                <td>{{ $revision->approved_at ? \Carbon\Carbon::parse($revision->approved_at)->format('d M, Y') : '-' }}</td>

                                <td>
                                    @if($revision->status === 'pending')
                                        <a href="{{ route('employees.revisions.edit', [$employee->id, $revision->id]) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('employees.revisions.destroy', [$employee->id, $revision->id]) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this revision?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
