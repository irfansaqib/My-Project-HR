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
    
    {{-- ID TYPE SELECTION --}}
    <div class="col-md-2">
        <div class="form-group">
            <label>ID Type <span class="text-danger">*</span></label>
            <div class="mt-2">
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_ntn" name="id_type" value="NTN" class="custom-control-input" 
                        {{ (old('id_type', $client->id_type ?? 'NTN') == 'NTN') ? 'checked' : '' }} onchange="toggleIdType()">
                    <label class="custom-control-label" for="type_ntn">NTN</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_cnic" name="id_type" value="CNIC" class="custom-control-input"
                        {{ (old('id_type', $client->id_type ?? '') == 'CNIC') ? 'checked' : '' }} onchange="toggleIdType()">
                    <label class="custom-control-label" for="type_cnic">CNIC</label>
                </div>
            </div>
        </div>
    </div>

    {{-- DYNAMIC INPUT FIELD --}}
    <div class="col-md-4">
        <div class="form-group">
            <label id="lbl_id_no">NTN Number <span class="text-danger">*</span></label>
            <input type="text" name="ntn_cnic" id="inp_id_no" class="form-control" 
                   value="{{ old('ntn_cnic', $client->ntn_cnic ?? '') }}" 
                   placeholder="A123456-8" required oninput="formatIdInput(this)">
            <small class="text-muted" id="hlp_id_no">Format: 7 Alphanumeric + Hyphen + 1 Check Digit</small>
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
<div class="alert alert-info small">
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

<div class="text-right mt-4">
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save mr-1"></i> {{ isset($client) ? 'Update Client' : 'Register Client' }}
    </button>
</div>

<script>
    function toggleIdType() {
        const isCnic = document.getElementById('type_cnic').checked;
        const lbl = document.getElementById('lbl_id_no');
        const inp = document.getElementById('inp_id_no');
        const hlp = document.getElementById('hlp_id_no');

        if (isCnic) {
            lbl.innerHTML = 'CNIC Number <span class="text-danger">*</span>';
            inp.placeholder = '3120225252641';
            inp.maxLength = 13;
            hlp.innerText = 'Format: 13 Numeric Digits (No dashes)';
        } else {
            lbl.innerHTML = 'NTN Number <span class="text-danger">*</span>';
            inp.placeholder = 'A123456-8';
            inp.maxLength = 9;
            hlp.innerText = 'Format: 7 Alphanumeric + Hyphen + 1 Digit';
        }
        // Don't clear input on toggle to avoid annoying user if they clicked wrong,
        // but re-validate immediately on next input
    }

    function formatIdInput(el) {
        const isCnic = document.getElementById('type_cnic').checked;
        let val = el.value.toUpperCase();

        if (isCnic) {
            // Allow only numbers
            el.value = val.replace(/[^0-9]/g, '').slice(0, 13);
        } else {
            // NTN Logic: Allow AlphaNumeric
            let clean = val.replace(/[^A-Z0-9]/g, '');
            if (clean.length > 7) {
                // Auto insert hyphen: XXXXXXX-X
                el.value = clean.slice(0, 7) + '-' + clean.slice(7, 8);
            } else {
                el.value = clean;
            }
        }
    }

    // Initialize on load (for Edit mode)
    document.addEventListener("DOMContentLoaded", function() {
        toggleIdType();
    });
</script>