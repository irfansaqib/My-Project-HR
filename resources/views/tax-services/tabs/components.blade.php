@extends('layouts.tax_client')

@section('tab-content')
<div class="row">
    {{-- LEFT: COMPONENTS LIST --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Salary Structure</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Component Name</th>
                            <th>Type</th>
                            <th>Tax Treatment</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->components as $comp)
                        <tr>
                            <td class="font-weight-bold">{{ $comp->name }}</td>
                            <td>
                                @if($comp->type == 'allowance') <span class="badge badge-primary">Allowance</span>
                                @else <span class="badge badge-warning">Deduction</span> @endif
                            </td>
                            <td>
                                @if($comp->is_tax_exempt) 
                                    <span class="badge badge-success">Exempt</span> 
                                    <small class="d-block text-muted mt-1">
                                        @if($comp->exemption_type == 'percentage_of_basic') {{ $comp->exemption_value }}% of Basic
                                        @else Fixed: {{ $comp->exemption_value }} @endif
                                    </small>
                                @else 
                                    <span class="badge badge-secondary">Taxable</span> 
                                @endif
                            </td>
                            <td class="text-right">
                                @if($comp->name == 'Basic Salary')
                                    <i class="fas fa-lock text-muted mr-2" title="System Component"></i>
                                @else
                                    {{-- EDIT BUTTON --}}
                                    <button class="btn btn-sm btn-outline-info mr-1" onclick='editComponent(@json($comp))'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- DELETE BUTTON --}}
                                    <form action="{{ route('tax-services.components.destroy', [$client->id, $comp->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this component?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No components found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- RIGHT: ADD NEW COMPONENT --}}
    <div class="col-md-4">
        <div class="card shadow-sm border-left-success">
            <div class="card-header bg-white">
                <h6 class="m-0 font-weight-bold text-success">Add New Component</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-services.components.store', $client->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. House Rent" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" onchange="toggleExemption(this.value, 'add')">
                            <option value="allowance">Allowance (Adds to Salary)</option>
                            <option value="deduction">Deduction (Subtracts)</option>
                        </select>
                    </div>

                    <div class="form-group" id="tax_treatment_div_add">
                        <label>Tax Treatment</label>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_tax_exempt" value="0">
                            <input type="checkbox" class="custom-control-input" id="exemptSwitchAdd" name="is_tax_exempt" value="1" onchange="toggleExemptDetails(this.checked, 'add')">
                            <label class="custom-control-label" for="exemptSwitchAdd">Is Tax Exempt?</label>
                        </div>
                    </div>

                    <div id="exemption_details_add" style="display: none;" class="bg-light p-3 rounded mb-3 border">
                        <div class="form-group">
                            <label class="small font-weight-bold">Exemption Rule</label>
                            <select name="exemption_type" class="form-control form-control-sm">
                                <option value="percentage_of_basic">% of Basic Salary</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Value</label>
                            <input type="number" step="0.01" name="exemption_value" class="form-control form-control-sm">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block font-weight-bold">
                        <i class="fas fa-plus-circle mr-1"></i> Add Component
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT COMPONENT MODAL --}}
<div class="modal fade" id="editComponentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="editComponentForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Edit Component</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" id="edit_type" class="form-control" onchange="toggleExemption(this.value, 'edit')">
                            <option value="allowance">Allowance</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>

                    <div class="form-group" id="tax_treatment_div_edit">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_tax_exempt" value="0">
                            <input type="checkbox" class="custom-control-input" id="exemptSwitchEdit" name="is_tax_exempt" value="1" onchange="toggleExemptDetails(this.checked, 'edit')">
                            <label class="custom-control-label" for="exemptSwitchEdit">Is Tax Exempt?</label>
                        </div>
                    </div>

                    <div id="exemption_details_edit" style="display: none;" class="bg-light p-3 rounded mb-3 border">
                        <div class="form-group">
                            <label>Exemption Rule</label>
                            <select name="exemption_type" id="edit_exemption_type" class="form-control form-control-sm">
                                <option value="percentage_of_basic">% of Basic Salary</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Value</label>
                            <input type="number" step="0.01" name="exemption_value" id="edit_exemption_value" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Update Component</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Visibility based on Type (Allowance/Deduction)
    function toggleExemption(type, mode) {
        let div = document.getElementById('tax_treatment_div_' + mode);
        let details = document.getElementById('exemption_details_' + mode);
        
        if (type === 'deduction') {
            div.style.display = 'none';
            details.style.display = 'none';
            // Uncheck switch implicitly
            document.getElementById('exemptSwitch' + (mode === 'add' ? 'Add' : 'Edit')).checked = false;
        } else {
            div.style.display = 'block';
            // Re-trigger details check
            let isChecked = document.getElementById('exemptSwitch' + (mode === 'add' ? 'Add' : 'Edit')).checked;
            details.style.display = isChecked ? 'block' : 'none';
        }
    }

    // Toggle Exemption Details fields
    function toggleExemptDetails(isChecked, mode) {
        document.getElementById('exemption_details_' + mode).style.display = isChecked ? 'block' : 'none';
    }

    // Populate Edit Modal
    function editComponent(comp) {
        let form = document.getElementById('editComponentForm');
        form.action = "{{ url('tax-services/clients/' . $client->id . '/components') }}/" + comp.id;

        document.getElementById('edit_name').value = comp.name;
        document.getElementById('edit_type').value = comp.type;
        
        // Handle Switch
        let switchEl = document.getElementById('exemptSwitchEdit');
        switchEl.checked = comp.is_tax_exempt == 1;
        
        // Populate Exemption fields
        document.getElementById('edit_exemption_type').value = comp.exemption_type || 'percentage_of_basic';
        document.getElementById('edit_exemption_value').value = comp.exemption_value || '';

        // Trigger logic to show/hide fields correctly
        toggleExemption(comp.type, 'edit');
        toggleExemptDetails(comp.is_tax_exempt == 1, 'edit');

        $('#editComponentModal').modal('show');
    }
</script>
@endsection