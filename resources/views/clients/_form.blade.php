<h6 class="heading-small text-muted mb-4">Business Information</h6>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Business / Company Name <span class="text-danger">*</span></label>
            <input type="text" name="business_name" class="form-control" 
                   value="{{ old('business_name', $client->business_name ?? '') }}" 
                   placeholder="e.g. ABC Solutions Pvt Ltd" required>
        </div>
    </div>
    
    {{-- BUSINESS TYPE DROPDOWN (Matches Portal) --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Business Type <span class="text-danger">*</span></label>
            <select name="business_type" id="business_type" class="form-control" required onchange="toggleIdentityFields()">
                <option value="" disabled {{ old('business_type', $client->business_type ?? '') ? '' : 'selected' }}>-- Select Entity Type --</option>
                <option value="Individual" {{ old('business_type', $client->business_type ?? '') == 'Individual' ? 'selected' : '' }}>Individual / Sole Proprietor</option>
                <option value="Partnership" {{ old('business_type', $client->business_type ?? '') == 'Partnership' ? 'selected' : '' }}>Partnership Firm</option>
                <option value="Company" {{ old('business_type', $client->business_type ?? '') == 'Company' ? 'selected' : '' }}>Company (Pvt Ltd / Ltd)</option>
            </select>
        </div>
    </div>
</div>

<div class="p-3 bg-secondary rounded mb-4">
    <h6 class="text-white mb-3" style="font-size: 0.9rem;"><i class="fas fa-id-card mr-1"></i> Identity & Registration Details</h6>
    
    <div class="row">
        {{-- CNIC FIELD (Visible for Individual) --}}
        <div class="col-md-6" id="cnic_field" style="display:none;">
            <div class="form-group">
                <label class="text-white">CNIC Number <span class="text-danger">*</span></label>
                <input type="text" name="cnic" id="cnic_input" class="form-control" 
                       value="{{ old('cnic', $client->cnic ?? '') }}" 
                       placeholder="42101-1234567-1" maxlength="15">
                <small class="text-white-50">Format: 13 Digits (Auto-dashed)</small>
            </div>
        </div>

        {{-- REGISTRATION NUMBER (Visible for Company/Partnership) --}}
        <div class="col-md-6" id="reg_no_field" style="display:none;">
            <div class="form-group">
                <label class="text-white">Registration Number <span class="text-danger">*</span></label>
                <input type="text" name="registration_number" id="reg_input" class="form-control" 
                       value="{{ old('registration_number', $client->registration_number ?? '') }}" 
                       placeholder="e.g. SECP Registration">
            </div>
        </div>

        {{-- NTN FIELD (Always Visible, Requirement Changes) --}}
        <div class="col-md-6" id="ntn_field" style="display:none;">
            <div class="form-group">
                <label class="text-white">
                    NTN Number 
                    <span id="ntn_required_star" class="text-danger" style="display:none;">*</span>
                    <span id="ntn_optional_badge" class="badge badge-light text-dark ml-1" style="display:none;">Optional</span>
                </label>
                <input type="text" name="ntn" id="ntn_input" class="form-control" 
                       value="{{ old('ntn', $client->ntn ?? '') }}" 
                       placeholder="1234567-8" maxlength="9">
                <small class="text-white-50">Format: 7 Digits + Check Digit</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Industry / Sector</label>
            <select name="industry" class="form-control">
                <option value="">-- Select Industry --</option>
                @foreach(['IT & Software', 'Manufacturing', 'Retail', 'Services', 'Construction', 'Other'] as $ind)
                    <option value="{{ $ind }}" {{ (old('industry', $client->industry ?? '') == $ind) ? 'selected' : '' }}>
                        {{ $ind }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active" {{ (old('status', $client->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (old('status', $client->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>

<hr class="my-4">

<h6 class="heading-small text-muted mb-4">Contact Person & Portal Access</h6>
@if(!isset($client))
<div class="alert alert-info small shadow-sm">
    <i class="fas fa-info-circle mr-1"></i> 
    An account will be created for the client to access the <strong>Client Portal</strong>. 
    Default password: <code>12345678</code>
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Contact Person Name <span class="text-danger">*</span></label>
            <input type="text" name="contact_person" class="form-control" 
                   value="{{ old('contact_person', $client->contact_person ?? '') }}" 
                   placeholder="Primary Contact" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Email Address (Login ID) <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" 
                   value="{{ old('email', $client->email ?? '') }}" 
                   placeholder="client@example.com" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control" 
                   value="{{ old('phone', $client->phone ?? '') }}" 
                   placeholder="+92 300 1234567">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Office Address</label>
            <textarea name="address" class="form-control" rows="1" placeholder="Full address...">{{ old('address', $client->address ?? '') }}</textarea>
        </div>
    </div>
</div>

<hr class="my-4">

<h6 class="heading-small text-primary mb-4"><i class="fas fa-robot mr-1"></i> Automation & Assignment</h6>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="default_employee_id" class="font-weight-bold">
                Default Account Manager / Assignee
            </label>
            
            <select name="default_employee_id" id="default_employee_id" class="form-control select2">
                <option value="">-- No Auto-Assignment (Manual) --</option>
                @if(isset($employees))
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                            {{ (old('default_employee_id', $client->default_employee_id ?? '') == $employee->id) ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->designation->title ?? 'Staff' }})
                        </option>
                    @endforeach
                @endif
            </select>
            <small class="form-text text-muted">
                <i class="fas fa-info-circle text-info"></i> 
                Any new request (Task) created by this client will be <strong>automatically assigned</strong> to this employee.
            </small>
        </div>
    </div>
</div>

<div class="text-right mt-4">
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save mr-1"></i> {{ isset($client) ? 'Update Client' : 'Register Client' }}
    </button>
</div>

{{-- JAVASCRIPT LOGIC (MATCHING PORTAL EXACTLY) --}}
<script>
    function toggleIdentityFields() {
        var type = document.getElementById("business_type").value;
        
        var cnicField = document.getElementById("cnic_field");
        var regField = document.getElementById("reg_no_field");
        var ntnField = document.getElementById("ntn_field");
        
        var ntnStar = document.getElementById("ntn_required_star");
        var ntnBadge = document.getElementById("ntn_optional_badge");
        
        var cnicInput = document.getElementById("cnic_input");
        var regInput = document.getElementById("reg_input");

        // Reset visibility
        cnicField.style.display = "none";
        regField.style.display = "none";
        ntnField.style.display = "block"; 

        if (type === "Individual") {
            // SHOW CNIC, HIDE REG
            cnicField.style.display = "block";
            cnicInput.required = true;
            regInput.required = false;

            // NTN OPTIONAL
            ntnStar.style.display = "none";
            ntnBadge.style.display = "inline-block";
        } else {
            // HIDE CNIC, SHOW REG
            regField.style.display = "block";
            regInput.required = true;
            cnicInput.required = false;

            // NTN REQUIRED
            ntnStar.style.display = "inline";
            ntnBadge.style.display = "none";
        }
    }

    // --- INPUT MASKING (Matched with Portal) ---
    document.addEventListener("DOMContentLoaded", function() {
        
        // Initial Run (for Edit mode)
        if(document.getElementById("business_type").value) { toggleIdentityFields(); }

        // CNIC MASK
        const cnicInput = document.getElementById('cnic_input');
        if(cnicInput) {
            cnicInput.addEventListener('input', function (e) {
                var x = e.target.value.replace(/\D/g, '').match(/(\d{0,5})(\d{0,7})(\d{0,1})/);
                e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
            });
        }

        // NTN MASK
        const ntnInput = document.getElementById('ntn_input');
        if(ntnInput) {
            ntnInput.addEventListener('input', function (e) {
                let val = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
                if (val.length > 7) {
                    e.target.value = val.substring(0, 7) + '-' + val.substring(7, 8);
                } else {
                    e.target.value = val;
                }
            });
        }
    });
</script>