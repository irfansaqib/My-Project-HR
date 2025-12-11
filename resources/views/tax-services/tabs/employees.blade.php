@extends('layouts.tax_client')

@section('tab-content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold text-gray-800">Employee Management</h5>
    <div>
        <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addEmployeeModal">
            <i class="fas fa-plus fa-sm"></i> Add New Employee
        </button>
        <a href="{{ route('tax-services.clients.export-employees', $client->id) }}" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-download fa-sm"></i> Export CSV
        </a>
        <button class="btn btn-sm btn-info shadow-sm" data-toggle="modal" data-target="#importEmployeeModal">
            <i class="fas fa-file-upload fa-sm"></i> Import CSV
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover" width="100%">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>CNIC</th>
                <th>Designation</th>
                <th>Joining Date</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->employees as $employee)
            <tr class="{{ $employee->status == 'resigned' ? 'table-secondary text-muted' : '' }}">
                <td class="font-weight-bold">{{ $employee->name }}</td>
                <td>{{ $employee->cnic }}</td>
                <td>{{ $employee->designation ?? '-' }}</td>
                <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : '-' }}</td>
                <td>
                    @if($employee->status == 'active')
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Resigned ({{ $employee->exit_date ? \Carbon\Carbon::parse($employee->exit_date)->format('d M, Y') : '' }})</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($employee->status == 'active')
                        <button type="button" class="btn btn-danger btn-sm btn-exit" 
                                data-id="{{ $employee->id }}" 
                                data-name="{{ $employee->name }}" 
                                data-toggle="modal" data-target="#exitEmployeeModal">
                            <i class="fas fa-user-times mr-1"></i> Exit/Delete
                        </button>
                    @else
                        <button class="btn btn-secondary btn-sm" disabled><i class="fas fa-lock"></i></button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('tax-services.employees.store', $client->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>CNIC <span class="text-danger">*</span></label>
                        <input type="text" name="cnic" class="form-control" required placeholder="00000-0000000-0">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Designation</label>
                                <input type="text" name="designation" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Joining Date <span class="text-danger">*</span></label>
                                <input type="date" name="joining_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="exitEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="exitForm" method="POST">
            @csrf @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Exit or Delete Employee</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>You are about to remove <strong id="exitEmpName"></strong>.</p>
                    
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i> 
                        If this employee has payroll history, they will be marked as <strong>Resigned</strong>. 
                        If no history exists, they will be <strong>Deleted</strong> permanently.
                    </div>

                    <div class="form-group">
                        <label>Exit Date (Required for Resignation)</label>
                        <input type="date" name="exit_date" class="form-control">
                        <small class="text-muted">Leave blank only if you intend to delete a mistake (requires no history).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Action</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="importEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('tax-services.employees.import', $client->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Import CSV</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <p class="small text-muted">CSV Format: Name, CNIC, Designation, Joining Date (YYYY-MM-DD)</p>
                    <input type="file" name="file" class="form-control-file" required accept=".csv">
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        const exitButtons = document.querySelectorAll('.btn-exit');
        const exitForm = document.getElementById('exitForm');
        const exitNameSpan = document.getElementById('exitEmpName');
        
        exitButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                let empId = this.getAttribute('data-id');
                let empName = this.getAttribute('data-name');
                let actionUrl = "{{ route('tax-services.employees.delete', ['client' => $client->id, 'employee' => ':id']) }}";
                actionUrl = actionUrl.replace(':id', empId);
                
                exitForm.action = actionUrl;
                exitNameSpan.innerText = empName;
            });
        });
    });
</script>

@endsection