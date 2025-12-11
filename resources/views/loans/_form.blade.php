{{-- resources/views/loans/_form.blade.php --}}

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

    <div class="row">
        {{-- Employee Selection --}}
        <div class="col-md-6 form-group">
            <label for="employee_id">Employee <span class="text-danger">*</span></label>
            {{-- ✅ FIX: Removed 'required' attribute here (handled by backend) to prevent Select2 conflict --}}
            <select name="employee_id" id="employee_id" class="form-control select2">
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id', $loan->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} ({{ $employee->employee_number ?? 'No ID' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Type Selection --}}
        <div class="col-md-6 form-group">
            <label>Type <span class="text-danger">*</span></label>
            <div class="mt-2">
                <div class="icheck-primary d-inline mr-4">
                    <input type="radio" id="type_advance" name="type" value="advance" 
                           {{ old('type', $loan->type ?? 'advance') == 'advance' ? 'checked' : '' }}>
                    <label for="type_advance">Salary Advance</label>
                </div>
                <div class="icheck-primary d-inline">
                    <input type="radio" id="type_loan" name="type" value="loan" 
                           {{ old('type', $loan->type ?? '') == 'loan' ? 'checked' : '' }}>
                    <label for="type_loan">Long-term Loan</label>
                </div>
            </div>
            @if(isset($loan))
                <input type="hidden" name="type" value="{{ $loan->type }}">
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="amount">Amount (PKR) <span class="text-danger">*</span></label>
            <input type="number" name="amount" id="amount" class="form-control" 
                   value="{{ old('amount', $loan->total_amount ?? '') }}" min="1">
        </div>

        <div class="col-md-4 form-group">
            <label for="loan_date">Date of Issue <span class="text-danger">*</span></label>
            <input type="date" name="loan_date" id="loan_date" class="form-control" 
                   value="{{ old('loan_date', isset($loan) ? $loan->loan_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
            <small class="text-muted">Date when the money was disbursed.</small>
        </div>

        <div class="col-md-4 form-group">
            <label for="start_date">Deduction Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date" class="form-control" 
                   value="{{ old('start_date', isset($loan) ? $loan->repayment_start_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
            <small class="text-muted">Payroll month to start deduction.</small>
        </div>
    </div>
    
    <div class="row" id="installments_wrapper" style="display: none;">
        <div class="col-md-4 form-group">
            <label for="installments">Installments (Months) <span class="text-danger">*</span></label>
            <input type="number" name="installments" id="installments" class="form-control" 
                   value="{{ old('installments', $loan->installments ?? 12) }}" min="2">
            <small class="text-primary font-weight-bold" id="monthly_deduction_hint"></small>
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Reason / Notes</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $loan->notes ?? '') }}</textarea>
    </div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ isset($loan) ? 'Update Request' : 'Submit Request' }}</button>
</div>

@push('scripts')
<script>
$(function() {
    function toggleType() {
        let isLoan = $('#type_loan').is(':checked') || $('input[name="type"][value="loan"]').is(':checked');
        
        if (isLoan) {
            $('#installments_wrapper').slideDown();
            // We remove JS required property to rely on server validation
            // This prevents browser from blocking submit if hidden
        } else {
            $('#installments_wrapper').slideUp();
        }
        calculateMonthly();
    }

    function calculateMonthly() {
        let amount = parseFloat($('#amount').val()) || 0;
        let months = 1;
        let isLoan = $('#type_loan').is(':checked') || $('input[name="type"][value="loan"]').is(':checked');

        if (isLoan) {
            months = parseInt($('#installments').val()) || 1;
        }

        let monthly = amount / months;
        if(monthly > 0 && months > 1) {
            $('#monthly_deduction_hint').text('Monthly Deduction: Rs. ' + monthly.toFixed(2));
        } else {
            $('#monthly_deduction_hint').text('');
        }
    }

    $('input[name="type"]').on('change', toggleType);
    $('#amount, #installments').on('input', calculateMonthly);
    toggleType();
});
</script>
@endpush