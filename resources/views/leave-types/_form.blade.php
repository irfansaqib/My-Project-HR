@csrf

{{-- 1. General Info --}}
<div class="form-group">
    <label for="name">Leave Type Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
           value="{{ old('name', $leaveType->name ?? '') }}" placeholder="e.g. Annual Leave" required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<hr>

{{-- 2. Policy Configuration (Hidden by default unless checked) --}}
<h5 class="text-primary mb-3"><i class="fas fa-coins"></i> Encashment Policy</h5>

<div class="form-group">
    <div class="custom-control custom-switch">
        {{-- Check if old input exists (validation fail) OR if database value is 1 --}}
        <input type="checkbox" class="custom-control-input" id="is_encashable" name="is_encashable" value="1"
               {{ old('is_encashable', $leaveType->is_encashable ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_encashable">Is this Leave Type Encashable?</label>
    </div>
    <small class="text-muted">Enable this if employees can convert unused leaves into cash.</small>
</div>

{{-- Wrapper for Policy Settings --}}
<div id="policy_settings" style="display: none;">
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Calculation Variable</label>
            <select name="encashment_variable" class="form-control">
                <option value="basic_salary" {{ old('encashment_variable', $leaveType->encashment_variable ?? 'basic_salary') == 'basic_salary' ? 'selected' : '' }}>Basic Salary</option>
                <option value="gross_salary" {{ old('encashment_variable', $leaveType->encashment_variable ?? '') == 'gross_salary' ? 'selected' : '' }}>Gross Salary</option>
            </select>
            <small class="text-muted">Which salary part is used to calculate 1 day's pay?</small>
        </div>

        <div class="col-md-6 form-group">
            <label>Calculation Divisor</label>
            <input type="number" name="encashment_divisor" class="form-control" 
                   value="{{ old('encashment_divisor', $leaveType->encashment_divisor ?? 30) }}">
            <small class="text-muted">Standard days in month (30 is standard, 26 excludes Sundays).</small>
        </div>
    </div>

    <div class="alert alert-light border p-2">
        <small><i class="fas fa-info-circle"></i> <strong>Formula:</strong> 1 Leave Day Amount = (Selected Variable) ÷ (Divisor)</small>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Minimum Balance Required</label>
            <input type="number" name="min_balance_required" class="form-control" 
                   value="{{ old('min_balance_required', $leaveType->min_balance_required ?? 0) }}">
            <small class="text-muted">Days that must remain in account (cannot be encashed).</small>
        </div>

        <div class="col-md-6 form-group">
            <label>Max Days Per Request</label>
            <input type="number" name="max_days_encashable" class="form-control" 
                   value="{{ old('max_days_encashable', $leaveType->max_days_encashable ?? 15) }}">
            <small class="text-muted">Maximum leaves an employee can encash at one time.</small>
        </div>
    </div>
</div>

<hr>

<button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Submit' }}</button>
<a href="{{ route('leave-types.index') }}" class="btn btn-secondary">Cancel</a>

{{-- Scripts pushed to the parent layout --}}
@push('scripts')
<script>
    $(document).ready(function() {
        function togglePolicySettings() {
            if($('#is_encashable').is(':checked')) {
                $('#policy_settings').slideDown();
            } else {
                $('#policy_settings').slideUp();
            }
        }

        // Run on change
        $('#is_encashable').change(function() {
            togglePolicySettings();
        });

        // Run on load (in case of Edit mode or validation error reprint)
        togglePolicySettings();
    });
</script>
@endpush