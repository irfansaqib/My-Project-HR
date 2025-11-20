@extends('layouts.admin')
@section('title', 'Pending Salary Approvals')

@section('content')
<div class_card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h3 class="card-title mb-0">Pending Salary Revisions</h3>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="thead-light text-center">
                    <tr>
                        <th class="text-left">Employee</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th class="text-right">New Basic (Rs)</th>
                        <th>Effective Date</th>
                        <th>Submitted By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($revisions as $revision)
                        <tr>
                            <td class="text-left">{{ $revision->employee->name ?? 'N/A' }}</td>
                            
                            {{-- ✅ N/A BUG FIX: Use the simple string properties --}}
                            <td>{{ $revision->employee->designation ?? 'N/A' }}</td>
                            <td>{{ $revision->employee->department ?? 'N/A' }}</td>

                            <td class="text-right">{{ number_format($revision->basic_salary, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($revision->effective_date)->format('d M, Y') }}</td>
                            <td>{{ $revision->creator->name ?? 'N/A' }}</td>
                            
                            <td>
                                <a href="{{ route('approvals.salary.show', $revision->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                There are no pending salary revisions for approval.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection