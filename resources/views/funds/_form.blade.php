<div class="card-body">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-group">
        <label for="name">Fund Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $fund->name ?? '') }}" placeholder="e.g. Employees Provident Fund" required>
    </div>

    <div class="form-group">
        <label for="salary_component_id">Linked Deduction Component <span class="text-danger">*</span></label>
        <select name="salary_component_id" class="form-control select2" required>
            <option value="">-- Select Deduction --</option>
            @foreach($components as $comp)
                <option value="{{ $comp->id }}" {{ old('salary_component_id', $fund->salary_component_id ?? '') == $comp->id ? 'selected' : '' }}>
                    {{ $comp->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Only Deductions marked as "Contributory Fund" appear here.</small>
    </div>

    <hr>
    <h5 class="text-primary">Employer Contribution Rules</h5>

    <div class="form-group">
        <label>Contribution Type</label>
        <div class="mt-2">
            <div class="custom-control custom-radio mb-2">
                <input type="radio" id="type_match" name="employer_contribution_type" class="custom-control-input" value="match_employee" 
                       {{ old('employer_contribution_type', $fund->employer_contribution_type ?? 'match_employee') == 'match_employee' ? 'checked' : '' }}>
                <label class="custom-control-label" for="type_match">Match Employee's Deduction (100%)</label>
            </div>
            <div class="custom-control custom-radio mb-2">
                <input type="radio" id="type_percent" name="employer_contribution_type" class="custom-control-input" value="percentage_of_basic"
                       {{ old('employer_contribution_type', $fund->employer_contribution_type ?? '') == 'percentage_of_basic' ? 'checked' : '' }}>
                <label class="custom-control-label" for="type_percent">Percentage of Basic Salary</label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" id="type_fixed" name="employer_contribution_type" class="custom-control-input" value="fixed_amount"
                       {{ old('employer_contribution_type', $fund->employer_contribution_type ?? '') == 'fixed_amount' ? 'checked' : '' }}>
                <label class="custom-control-label" for="type_fixed">Fixed Amount</label>
            </div>
        </div>
    </div>

    <div class="form-group" id="value_input_wrapper" style="display: none;">
        <label for="employer_contribution_value">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="employer_contribution_value" id="employer_contribution_value" class="form-control" 
               value="{{ old('employer_contribution_value', $fund->employer_contribution_value ?? '') }}">
        <small class="text-muted" id="value_hint"></small>
    </div>

    <div class="form-group">
        <label for="description">Description / Notes</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $fund->description ?? '') }}</textarea>
    </div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('funds.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save Configuration</button>
</div>

@push('scripts')
<script>
    $(function() {
        function toggleValueInput() {
            let type = $('input[name="employer_contribution_type"]:checked').val();
            let wrapper = $('#value_input_wrapper');
            let hint = $('#value_hint');
            
            if (type === 'match_employee') {
                wrapper.slideUp();
                $('#employer_contribution_value').prop('required', false);
            } else {
                wrapper.slideDown();
                $('#employer_contribution_value').prop('required', true);
                
                if (type === 'percentage_of_basic') {
                    hint.text('Enter percentage (e.g., 10 for 10%)');
                } else {
                    hint.text('Enter fixed amount (e.g., 5000)');
                }
            }
        }

        $('input[name="employer_contribution_type"]').change(toggleValueInput);
        toggleValueInput();
    });
</script>
@endpush