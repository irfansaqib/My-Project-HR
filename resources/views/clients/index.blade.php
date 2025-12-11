@extends('layouts.admin')
@section('title', 'Client Management')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-users-cog mr-2"></i> Client List</h5>
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Add New Client
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Client / Business</th>
                        <th>Contact Person</th>
                        <th>Assigned Team</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>
                            <strong class="text-dark">{{ $client->business_name }}</strong><br>
                            <small class="text-muted"><i class="fas fa-id-card mr-1"></i> {{ $client->ntn_cnic }}</small>
                        </td>
                        <td>
                            {{ $client->contact_person }} <br>
                            <small class="text-muted">{{ $client->email }}</small>
                        </td>
                        <td>
                            @if($client->assignments->count() > 0)
                                @foreach($client->assignments as $assign)
                                    <span class="badge badge-info mb-1">
                                        {{ $assign->employee->name }} ({{ $assign->service_type }})
                                    </span><br>
                                @endforeach
                            @else
                                <span class="text-muted small"><em>Unassigned</em></span>
                            @endif
                            <button class="btn btn-xs btn-outline-secondary mt-1" onclick="openAssignModal({{ $client->id }}, '{{ $client->business_name }}')">
                                <i class="fas fa-user-plus"></i> Assign
                            </button>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $client->status == 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($client->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                                    Options
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye mr-2 text-info"></i> View Profile</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-edit mr-2 text-warning"></i> Edit Details</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-trash mr-2"></i> Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i><br>
                            No clients found. Add your first client to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ASSIGNMENT MODAL --}}
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="assignForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Assign Team to <span id="modalClientName" class="text-warning"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach(\App\Models\Employee::where('status', 'active')->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->designation->title ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Service / Job Nature <span class="text-danger">*</span></label>
                        <select name="service_type" class="form-control" required>
                            <option value="">-- Select Service --</option>
                            <option value="Taxation">Taxation</option>
                            <option value="Accounting">Accounting & Bookkeeping</option>
                            <option value="Audit">Audit & Assurance</option>
                            <option value="Corporate">Corporate Compliance</option>
                            <option value="Legal">Legal Services</option>
                            <option value="General">General / Relationship Manager</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Employee</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal(clientId, clientName) {
        let form = document.getElementById('assignForm');
        // Dynamically set the route for the selected client
        form.action = "{{ url('clients') }}/" + clientId + "/assign";
        document.getElementById('modalClientName').innerText = clientName;
        $('#assignModal').modal('show');
    }
</script>
@endsection