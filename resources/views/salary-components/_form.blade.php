{{-- resources/views/salary-components/_form.blade.php --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 form-group">
        <label for="name">Component Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $salaryComponent->name ?? '') }}" required placeholder="e.g. Medical Allowance">
    </div>
    <div class="col-md-6 form-group">
        <label for="type">Component Type <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-control" required>
            <option value="allowance" @if(old('type', $salaryComponent->type ?? '') == 'allowance') selected @endif>Allowance (Earnings)</option>
            <option value="deduction" @if(old('type', $salaryComponent->type ?? '') == 'deduction') selected @endif>Deduction (Recovery)</option>
        </select>
    </div>
</div>

{{-- ==========================
     ALLOWANCE SETTINGS
=========================== --}}
<div id="allowance-settings" class="d-none">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Tax Exemption Settings</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_tax_exempt" name="is_tax_exempt" value="1"
                        {{ old('is_tax_exempt', $salaryComponent->is_tax_exempt ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_tax_exempt">Is this Allowance Tax Exempt?</label>
                </div>
            </div>

            <div id="exemption-details" class="{{ old('is_tax_exempt', $salaryComponent->is_tax_exempt ?? false) ? '' : 'd-none' }} mt-3 p-3 bg-light rounded border">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="exemption_type">Exemption Rule</label>
                        <select name="exemption_type" id="exemption_type" class="form-control">
                            <option value="percentage_of_basic" @if(old('exemption_type', $salaryComponent->exemption_type ?? '') == 'percentage_of_basic') selected @endif>Percentage of Basic Salary</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="exemption_value">Percentage Value (%)</label>
                        <input type="number" step="0.01" name="exemption_value" id="exemption_value" class="form-control" 
                               value="{{ old('exemption_value', $salaryComponent->exemption_value ?? '') }}" placeholder="e.g. 10">
                    </div>
                </div>
                <small class="text-muted"><i class="fas fa-info-circle"></i> Example: Medical Allowance is often exempt up to 10% of Basic Salary.</small>
            </div>
        </div>
    </div>
</div>

{{-- ==========================
     DEDUCTION SETTINGS
=========================== --}}
<div id="deduction-settings" class="d-none">
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">Deduction Category</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="d-block mb-3">Select the nature of this deduction:</label>

                {{-- 1. Standard --}}
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="cat_standard" name="deduction_category" class="custom-control-input" value="standard" checked>
                    <label class="custom-control-label" for="cat_standard">
                        <strong>Standard / Manual Deduction</strong>
                        <small class="d-block text-muted">e.g. Late Fines, Penalties</small>
                    </label>
                </div>
                
                {{-- ✅ 5. Contributory Fund --}}
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="cat_contributory" name="deduction_category" class="custom-control-input" value="contributory"
                        {{ old('is_contributory', $salaryComponent->is_contributory ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="cat_contributory">
                        <strong>Contributory Fund</strong>
                        <small class="d-block text-muted">e.g. Provident Fund, EOBI. Linked to the <em>Funds</em> module.</small>
                    </label>
                </div>

                {{-- 2. Income Tax --}}
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="cat_tax" name="deduction_category" class="custom-control-input" value="tax"
                        {{ old('is_tax_component', $salaryComponent->is_tax_component ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="cat_tax">
                        <strong>Income Tax</strong>
                        <small class="d-block text-muted">Marks this as the primary Tax deduction column.</small>
                    </label>
                </div>

                {{-- 3. Salary Advance --}}
                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="cat_advance" name="deduction_category" class="custom-control-input" value="advance"
                        {{ old('is_advance', $salaryComponent->is_advance ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="cat_advance">
                        <strong>Salary Advance</strong>
                        <small class="d-block text-muted">Linked to the <em>Loans & Advances</em> module.</small>
                    </label>
                </div>

                {{-- 4. Loan --}}
                <div class="custom-control custom-radio">
                    <input type="radio" id="cat_loan" name="deduction_category" class="custom-control-input" value="loan"
                        {{ old('is_loan', $salaryComponent->is_loan ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="cat_loan">
                        <strong>Long-term Loan Installment</strong>
                        <small class="d-block text-muted">Linked to the <em>Loans & Advances</em> module.</small>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Hidden inputs to map radio to booleans for controller --}}
    <input type="hidden" name="is_tax_component" id="input_is_tax_component" value="0">
    <input type="hidden" name="is_advance" id="input_is_advance" value="0">
    <input type="hidden" name="is_loan" id="input_is_loan" value="0">
    <input type="hidden" name="is_contributory" id="input_is_contributory" value="0"> {{-- ✅ Added --}}
</div>

@push('scripts')
<script>
$(function() {
    // Toggle Sections based on Type
    function toggleSections() {
        let type = $('#type').val();
        
        if (type === 'allowance') {
            $('#allowance-settings').removeClass('d-none');
            $('#deduction-settings').addClass('d-none');
        } else {
            $('#allowance-settings').addClass('d-none');
            $('#deduction-settings').removeClass('d-none');
        }
    }

    // Toggle Exemption Details
    $('#is_tax_exempt').change(function() {
        if(this.checked) $('#exemption-details').removeClass('d-none');
        else $('#exemption-details').addClass('d-none');
    });

    // Map Deduction Category Radio to Hidden Inputs
    function updateHiddenInputs() {
        // Reset all
        $('#input_is_tax_component').val(0);
        $('#input_is_advance').val(0);
        $('#input_is_loan').val(0);
        $('#input_is_contributory').val(0);

        let category = $('input[name="deduction_category"]:checked').val();

        if (category === 'tax') $('#input_is_tax_component').val(1);
        if (category === 'advance') $('#input_is_advance').val(1);
        if (category === 'loan') $('#input_is_loan').val(1);
        if (category === 'contributory') $('#input_is_contributory').val(1); // ✅ Added
    }

    // Listeners
    $('#type').change(toggleSections);
    $('input[name="deduction_category"]').change(updateHiddenInputs);

    // Initialization
    toggleSections();
    updateHiddenInputs();
});
</script>
@endpush