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
        <a href="{{ route('employees.create') }}" class="btn btn-primary float-right">Add New Employee</a>
    </div>
    <div class="card-body">
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
                        <td><span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($employee->status) }}</span></td>
                        <td>
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-xs btn-info">View</a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-xs btn-warning">Edit</a>
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection