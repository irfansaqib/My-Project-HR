@extends('layouts.admin')
@section('title', 'Client Management')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-users-cog mr-2"></i> Client List</h5>
        
        <div>
            {{-- 
                ✅ HANDSHAKE LINK GENERATOR
                Updated to check for 'portal_code' OR 'business_code' to prevent the error.
            --}}
            <button onclick="copyPortalLink()" class="btn btn-info btn-sm shadow-sm mr-2" title="Copy Client Registration Link">
                <i class="fas fa-link mr-1"></i> Copy Embed Link
            </button>

            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus-circle mr-1"></i> Add New Client
            </a>
        </div>
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
                            <small class="text-muted">
                                @if($client->business_type == 'Individual')
                                    <i class="fas fa-user mr-1"></i> Individual
                                @else
                                    <i class="fas fa-building mr-1"></i> Company
                                @endif
                            </small>
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
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Options
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('clients.show', $client->id) }}">
                                        <i class="fas fa-eye mr-2 text-info"></i> View Profile
                                    </a>
                                    
                                    <a class="dropdown-item" href="{{ route('clients.edit', $client->id) }}">
                                        <i class="fas fa-edit mr-2 text-warning"></i> Edit Details
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    
                                    <a class="dropdown-item text-danger" href="#" onclick="if(confirm('Are you sure you want to delete this client?')){ document.getElementById('delete-client-{{ $client->id }}').submit(); } return false;">
                                        <i class="fas fa-trash mr-2"></i> Delete
                                    </a>
                                    <form id="delete-client-{{ $client->id }}" action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
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

{{-- 
    HIDDEN INPUT FOR URL GENERATION 
    FIX: Checking for BOTH 'portal_code' and 'business_code' to avoid missing code error.
--}}
<input type="hidden" id="portalInviteLink" 
       value="{{ route('client.register') }}?code={{ Auth::user()->business->portal_code ?? Auth::user()->business->business_code ?? '' }}">

<script>
    function openAssignModal(clientId, clientName) {
        let form = document.getElementById('assignForm');
        form.action = "{{ url('clients') }}/" + clientId + "/assign";
        document.getElementById('modalClientName').innerText = clientName;
        
        // jQuery / Bootstrap 5 fallback check
        if (typeof $ !== 'undefined') {
            $('#assignModal').modal('show');
        } else {
            let myModal = new bootstrap.Modal(document.getElementById('assignModal'));
            myModal.show();
        }
    }

    function copyPortalLink() {
        var copyText = document.getElementById("portalInviteLink");
        var val = copyText.value;

        // Validation: If the code parameter is empty
        if(val.endsWith('code=') || val.endsWith('code=')) {
            // FIX: More detailed alert so you know exactly what is missing
            alert('⚠️ CRITICAL ERROR: Business Code Missing.\n\nIt seems your Business Profile does not have a "Portal Code" or "Business Code" generated yet.\n\nPlease go to Settings > Business Profile and click "Generate Code".');
            return;
        }

        navigator.clipboard.writeText(val).then(function() {
            alert('✅ Embed Link Copied!\n\nLink: ' + val + '\n\nYou can now paste this URL into your website buttons.');
        }, function(err) {
            console.error('Could not copy text: ', err);
            prompt("Copy this link manually:", val);
        });
    }
</script>
@endsection