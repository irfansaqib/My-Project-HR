@extends('layouts.admin')

@section('title', 'Employees')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employee List</h3>
        @can('manage employees')
            <a href="{{ route('employees.create') }}" class="btn btn-primary float-right">Add New Employee</a>
        @endcan
    </div>
    <div class="card-body">
        
        {{-- ✅ NEW: Status Filter Form --}}
        <form action="{{ route('employees.index') }}" method="GET" class="form-inline mb-3">
            <div class="form-group">
                <label for="status" class="mr-2">Filter by Status:</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="all" @if($currentStatus == 'all') selected @endif>All Employees</option>
                    <option value="active" @if($currentStatus == 'active') selected @endif>Active</option>
                    <option value="resigned" @if($currentStatus == 'resigned') selected @endif>Resigned</option>
                    <option value="terminated" @if($currentStatus == 'terminated') selected @endif>Terminated</option>
                    <option value="retired" @if($currentStatus == 'retired') selected @endif>Retired</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Emp. Number</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>CNIC</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th style="width: 180px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->employee_number }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->designation }}</td>
                        <td>{{ $employee->cnic }}</td>
                        <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</td>
                        <td>
                            @php
                                $statusClass = 'secondary'; // Default
                                if ($employee->status === 'active') $statusClass = 'success';
                                elseif (in_array($employee->status, ['resigned', 'terminated', 'retired'])) $statusClass = 'danger';
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">{{ ucfirst($employee->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-xs btn-info">View</a>
                            @if($employee->status === 'active')
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No employees found for the selected status.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection