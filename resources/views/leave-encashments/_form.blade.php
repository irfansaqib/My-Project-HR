@csrf

@php
    // 1. Check Permissions
    $isAdmin = Auth::user()->hasRole(['Owner', 'Admin']);
    
    // 2. Determine Lock Status
    // Lock if: (Editing an existing record) OR (User is NOT an Admin)
    $isLocked = isset($encashment) || !$isAdmin;

    // 3. Determine Selected Employee ID
    // Priority: Old Input -> Existing DB Value -> Logged In User's Employee ID -> Empty
    $loggedInEmployeeId = Auth::user()->employee ? Auth::user()->employee->id : '';
    $selectedEmployeeId = old('employee_id', $encashment->employee_id ?? ($isAdmin ? '' : $loggedInEmployeeId));
@endphp

<div class="row">
    <div class="col-md-6 form-group">
        <label>Employee <span class="text-danger">*</span></label>
        
        {{-- The Dropdown --}}
        <select name="employee_id" id="employee_id" class="form-control select2" required {{ $isLocked ? 'disabled' : '' }}>
            <option value="">-- Select Employee --</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>

        {{-- 
            CRITICAL: HTML <select disabled> elements are NOT submitted with the form.
            We must add a hidden input when locked so the Controller receives the ID.
        --}}
        @if($isLocked)
            <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
        @endif
    </div>

    <div class="col-md-6 form-group">
        <label>Leave Type <span class="text-danger">*</span></label>
        <select name="leave_type_id" id="leave_type_id" class="form-control" required>
            <option value="">-- Select Type --</option>
            @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}" {{ old('leave_type_id', $encashment->leave_type_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label>Days to Encash <span class="text-danger">*</span></label>
        <input type="number" step="0.5" name="days" id="days_input" class="form-control" 
               value="{{ old('days', $encashment->days ?? '') }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label>Date <span class="text-danger">*</span></label>
        <input type="date" name="encashment_date" class="form-control" 
               value="{{ old('encashment_date', isset($encashment) ? $encashment->encashment_date->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
</div>

{{-- Calculation Result Box --}}
<div id="calculation_result" class="alert alert-light border mt-3" style="display:none;">
    <div class="row text-center">
        <div class="col-md-3 border-right">
            <small class="text-muted">Current Balance</small>
            <h5 id="view_balance">-</h5>
        </div>
        <div class="col-md-3 border-right">
            <small class="text-muted">Per Day Rate</small>
            <h5 id="view_rate">-</h5>
        </div>
        <div class="col-md-3 border-right">
            <small class="text-muted">Max Allowed</small>
            <h5 id="view_limit">-</h5>
        </div>
        <div class="col-md-3">
            <small class="text-success font-weight-bold">Total Payout</small>
            <h3 class="text-success font-weight-bold" id="view_total">-</h3>
        </div>
    </div>
    {{-- Hidden field to store the calculated amount for DB --}}
    <input type="hidden" name="amount" id="hidden_amount" value="{{ old('amount', $encashment->amount ?? '') }}">
    <div id="error_msg" class="text-danger mt-2 text-center font-weight-bold" style="display:none;"></div>
</div>

<div class="form-group mt-3">
    <label>Notes</label>
    <input type="text" name="notes" class="form-control" placeholder="Optional notes..." 
           value="{{ old('notes', $encashment->notes ?? '') }}">
</div>

<hr>

<button type="submit" class="btn btn-success" id="btn_submit" disabled>{{ $buttonText ?? 'Submit Request' }}</button>
<a href="{{ route('leave-encashments.index') }}" class="btn btn-secondary">Cancel</a>

@push('scripts')
<script>
    $(document).ready(function() {
        if($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4' });
        }

        function calculateEstimate() {
            // Get ID from dropdown OR hidden input if dropdown is disabled/empty
            let empId = $('#employee_id').val();
            if(!empId && $('input[name="employee_id"]').length > 0) {
                empId = $('input[name="employee_id"]').val();
            }

            let typeId = $('#leave_type_id').val();
            let days = $('#days_input').val();

            if(empId && typeId && days) {
                $.ajax({
                    url: "{{ route('api.encashment.estimate') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        employee_id: empId,
                        leave_type_id: typeId,
                        days: days
                    },
                    success: function(res) {
                        $('#calculation_result').slideDown();
                        
                        if(res.error) {
                            $('#error_msg').text(res.error).show();
                            $('#btn_submit').prop('disabled', true);
                            $('#view_total').text('0.00');
                        } else {
                            $('#error_msg').hide();
                            $('#view_balance').text(res.current_balance);
                            $('#view_rate').text(res.per_day_rate);
                            $('#view_limit').text(res.available_limit);
                            $('#view_total').text(res.total_amount); // Formatted for display
                            $('#hidden_amount').val(res.raw_amount); // Raw for DB submission
                            $('#btn_submit').prop('disabled', false);
                        }
                    },
                    error: function() {
                        // alert('Error calculating salary details.');
                    }
                });
            }
        }

        // Trigger calculation on change
        $('#days_input, #leave_type_id, #employee_id').change(function() {
            calculateEstimate();
        });

        // Trigger on load (For Edit mode OR if Employee is auto-selected)
        calculateEstimate();
    });
</script>
@endpush