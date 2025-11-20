@extends('layouts.admin')
@section('title', 'Employees List')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-users mr-2 text-primary"></i> Employees Management</h4>
        <a href="{{ route('employees.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Add New Employee
        </a>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3 bg-light rounded">
            <form action="{{ route('employees.index') }}" method="GET" class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="form-group mb-0 mr-3">
                    <label for="status" class="mr-2 font-weight-bold text-secondary">Status:</label>
                    <select name="status" id="status" class="custom-select custom-select-sm" onchange="this.form.submit()" style="min-width: 120px;">
                        <option value="all" {{ $currentStatus == 'all' ? 'selected' : '' }}>All</option>
                        <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="resigned" {{ $currentStatus == 'resigned' ? 'selected' : '' }}>Resigned</option>
                        <option value="terminated" {{ $currentStatus == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="retired" {{ $currentStatus == 'retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>

                <div class="form-group mb-0 flex-grow-1" style="max-width: 400px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-right-0"
                            placeholder="Search by Name, CNIC, or Email...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="py-3 pl-3" width="4%">#</th>
                            <th class="py-3 text-center" width="6%">Photo</th>
                            <th class="py-3" width="24%">Employee Details</th>
                            {{-- ✅ RENAMED: "Position" --}}
                            <th class="py-3" width="20%">Position</th> 
                            <th class="py-3" width="22%">Contact Info</th>
                            <th class="py-3 text-right" width="14%">Gross Salary</th>
                            <th class="py-3 text-center" width="8%">Status</th>
                            <th class="py-3 text-center" width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $employee)
                            <tr>
                                <td class="pl-3 align-middle font-weight-bold text-secondary">{{ $employees->firstItem() + $index }}</td>
                                
                                {{-- Photo Column --}}
                                <td class="text-center align-middle">
                                    <img src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/150' }}"
                                         alt="Photo" 
                                         class="rounded-circle shadow-sm border" 
                                         style="width: 45px; height: 45px; object-fit: cover;">
                                </td>

                                {{-- Name & CNIC Column --}}
                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('employees.show', $employee->id) }}" class="text-dark font-weight-bold text-decoration-none mb-1">
                                            {{ $employee->name }}
                                        </a>
                                        <span class="text-muted small">
                                            <i class="fas fa-id-card mr-1 text-secondary"></i> {{ $employee->cnic ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- ✅ POSITION COLUMN (Designation over Department) --}}
                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark mb-1">{{ $employee->designation ?? '-' }}</span>
                                        <span class="text-muted small">
                                            <i class="far fa-building mr-1"></i> {{ $employee->department ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Contact Column --}}
                                <td class="align-middle">
                                    <div class="d-flex flex-column small">
                                        <span class="mb-1 text-dark">
                                            <i class="fas fa-phone-alt mr-1 text-success"></i> {{ $employee->phone ?? '-' }}
                                        </span>
                                        <span class="text-muted text-truncate" style="max-width: 220px;" title="{{ $employee->email }}">
                                            <i class="fas fa-envelope mr-1 text-info"></i> {{ $employee->email }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Gross Salary Column --}}
                                <td class="align-middle text-right font-weight-bold text-dark text-nowrap">
                                    <span class="text-muted mr-1">Rs.</span>{{ number_format($employee->gross_salary ?? 0, 2) }}
                                </td>

                                {{-- Status Column --}}
                                <td class="align-middle text-center">
                                    @php
                                        $badgeClass = [
                                            'active' => 'success',
                                            'resigned' => 'secondary',
                                            'terminated' => 'danger',
                                            'retired' => 'warning'
                                        ][$employee->status] ?? 'info';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} px-2 py-1 text-uppercase shadow-sm" style="font-size: 0.7rem;">
                                        {{ $employee->status }}
                                    </span>
                                </td>

                                {{-- Actions Column --}}
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm shadow-sm">
                                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-light border" title="View Profile">
                                            <i class="fas fa-eye text-info"></i>
                                        </a>
                                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-light border" title="Edit Details">
                                            <i class="fas fa-edit text-primary"></i>
                                        </a>
                                        <a href="{{ route('employees.print', $employee->id) }}" class="btn btn-light border" target="_blank" title="Print">
                                            <i class="fas fa-print text-secondary"></i>
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light border text-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-users-slash fa-3x mb-3 text-gray-300"></i>
                                        <h5>No employees found</h5>
                                        <p class="small mb-0">Try adjusting your search or filter settings.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($employees->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center border-top-0 py-3">
                {{ $employees->appends(['status' => $currentStatus, 'search' => request('search')])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection