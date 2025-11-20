<div class="alert alert-info">
    <strong>Note:</strong> Income Tax is calculated automatically by the system based on the defined tax slabs.
    You can optionally mark one <strong>deduction</strong> as the <strong>Tax Deduction</strong> for reporting purposes.
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="name">Component Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
               value="{{ old('name', $salaryComponent->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 form-group">
        <label for="type">Component Type <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
            <option value="allowance" @if(old('type', $salaryComponent->type ?? '') == 'allowance') selected @endif>Allowance</option>
            <option value="deduction" @if(old('type', $salaryComponent->type ?? '') == 'deduction') selected @endif>Deduction</option>
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- ✅ New: Visible only when type = deduction --}}
<div id="tax-component-section" class="form-check mt-3 {{ old('type', $salaryComponent->type ?? '') == 'deduction' ? '' : 'd-none' }}">
    <input type="checkbox" name="is_tax_component" id="is_tax_component" class="form-check-input"
           value="1" {{ old('is_tax_component', $salaryComponent->is_tax_component ?? false) ? 'checked' : '' }}>
    <label for="is_tax_component" class="form-check-label">
        Mark this deduction as <strong>Tax Deduction</strong> (only one allowed)
    </label>
</div>

<hr>
<h5 class="mt-3">Tax Settings (For Allowances)</h5>
<div class="form-check">
    <input type="checkbox" name="is_tax_exempt" id="is_tax_exempt" class="form-check-input"
           value="1" @if(old('is_tax_exempt', $salaryComponent->is_tax_exempt ?? false)) checked @endif>
    <label for="is_tax_exempt" class="form-check-label">This allowance is tax-exempt</label>
</div>

<div id="exemption-details" class="{{ old('is_tax_exempt', $salaryComponent->is_tax_exempt ?? false) ? '' : 'd-none' }} mt-3">
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="exemption_type">Exemption Type</label>
            <select name="exemption_type" id="exemption_type" class="form-control">
                <option value="percentage_of_basic"
                        @if(old('exemption_type', $salaryComponent->exemption_type ?? '') == 'percentage_of_basic') selected @endif>
                    Percentage of Basic Salary
                </option>
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="exemption_value">Exemption Percentage (%)</label>
            <input type="number" step="0.01" name="exemption_value" id="exemption_value"
                   class="form-control"
                   value="{{ old('exemption_value', $salaryComponent->exemption_value ?? '') }}">
        </div>
    </div>
    <p class="text-muted">
        <small>
            Example: If House Rent is exempt up to 45% of Basic Salary, select 'Percentage of Basic Salary'
            and enter '45' as the value.
        </small>
    </p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const taxComponentSection = document.getElementById('tax-component-section');
    const isExemptCheckbox = document.getElementById('is_tax_exempt');
    const exemptionDetailsDiv = document.getElementById('exemption-details');

    // Toggle visibility of Tax Deduction checkbox based on type
    function toggleTaxComponentSection() {
        if (typeSelect.value === 'deduction') {
            taxComponentSection.classList.remove('d-none');
        } else {
            taxComponentSection.classList.add('d-none');
            document.getElementById('is_tax_component').checked = false;
        }
    }

    // Toggle exemption details for allowances
    function toggleExemptionDetails() {
        if (isExemptCheckbox.checked) {
            exemptionDetailsDiv.classList.remove('d-none');
        } else {
            exemptionDetailsDiv.classList.add('d-none');
        }
    }

    // Initial states
    toggleTaxComponentSection();
    toggleExemptionDetails();

    // Listeners
    typeSelect.addEventListener('change', toggleTaxComponentSection);
    isExemptCheckbox.addEventListener('change', toggleExemptionDetails);
});
</script>
@endpush
