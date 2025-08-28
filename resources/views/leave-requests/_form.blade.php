<div class="card-body">
    <div class="row">
        <div class="col-md-12 form-group">
            <label for="leave_type">Leave Type <span class="text-danger">*</span></label>
            <select name="leave_type" id="leave_type" class="form-control" required>
                <option value="">Select a Leave Type</option>
                <option value="annual" @if(old('leave_type', $leaveRequest->leave_type ?? '') == 'annual') selected @endif>Annual ({{ $employee->leaves_annual_remaining ?? $employee->leaves_annual }} remaining)</option>
                <option value="sick" @if(old('leave_type', $leaveRequest->leave_type ?? '') == 'sick') selected @endif>Sick ({{ $employee->leaves_sick_remaining ?? $employee->leaves_sick }} remaining)</option>
                <option value="casual" @if(old('leave_type', $leaveRequest->leave_type ?? '') == 'casual') selected @endif>Casual ({{ $employee->leaves_casual_remaining ?? $employee->leaves_casual }} remaining)</option>
                <option value="other" @if(old('leave_type', $leaveRequest->leave_type ?? '') == 'other') selected @endif>Other ({{ $employee->leaves_other_remaining ?? $employee->leaves_other }} remaining)</option>
            </select>
            @error('leave_type') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label for="start_date">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control" id="start_date" value="{{ old('start_date', $leaveRequest->start_date ?? '') }}" required>
            @error('start_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 form-group">
            <label for="end_date">End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" class="form-control" id="end_date" value="{{ old('end_date', $leaveRequest->end_date ?? '') }}" required>
            @error('end_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 form-group">
            <label for="total_days">Total Days</label>
            <input type="text" class="form-control" id="total_days" readonly>
        </div>
    </div>
    <div class="form-group">
        <label for="reason">Reason <span class="text-danger">*</span></label>
        <textarea name="reason" id="reason" class="form-control" rows="4" required>{{ old('reason', $leaveRequest->reason ?? '') }}</textarea>
        @error('reason') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label for="attachment">Attach Document (Optional)</label>
        <div class="custom-file">
            <input type="file" name="attachment" class="custom-file-input" id="attachment">
            <label class="custom-file-label" for="attachment">Choose file</label>
        </div>
        @error('attachment') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const totalDaysInput = document.getElementById('total_days');

        function calculateDays() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (startDateInput.value && endDateInput.value && endDate >= startDate) {
                const timeDiff = endDate.getTime() - startDate.getTime();
                const dayDiff = (timeDiff / (1000 * 3600 * 24)) + 1;
                totalDaysInput.value = dayDiff;
            } else {
                totalDaysInput.value = 0;
            }
        }

        startDateInput.addEventListener('change', calculateDays);
        endDateInput.addEventListener('change', calculateDays);

        // Calculate on page load for the edit form
        calculateDays();
    });
</script>
@endpush