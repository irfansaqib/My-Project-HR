<div class="card-body">
    <div class="row">
        <div class="col-md-12 form-group">
            <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
            {{-- ✅ DEFINITIVE FIX: This dropdown is now built from the employee's specific allotted leaves. --}}
            <select name="leave_type_id" id="leave_type_id" class="form-control" required>
                <option value="">Select an Allotted Leave Type</option>
                {{-- Loop through the LeaveType models associated with the employee --}}
                @foreach($leaveTypes as $type)
                    @php
                        // Get the remaining balance calculated in the controller.
                        $slug = Illuminate\Support\Str::slug($type->name, '_');
                        $remainingKey = 'leaves_' . $slug . '_remaining';
                        $remaining = $employee->{$remainingKey} ?? 0;
                        // Check if this option should be selected (for edit forms).
                        $isSelected = old('leave_type_id', optional($leaveRequest ?? null)->leave_type) == $type->name;
                    @endphp
                    {{-- Only show leave types that have been allotted days --}}
                    @if($type->pivot->days_allotted > 0)
                        <option value="{{ $type->id }}" @if($isSelected) selected @endif>
                            {{ $type->name }} ({{ $remaining }} remaining)
                        </option>
                    @endif
                @endforeach
            </select>
            @error('leave_type_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label for="start_date">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control" id="start_date" value="{{ old('start_date', isset($leaveRequest) ? \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') : '') }}" required>
            @error('start_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 form-group">
            <label for="end_date">End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" class="form-control" id="end_date" value="{{ old('end_date', isset($leaveRequest) ? \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d') : '') }}" required>
            @error('end_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 form-group">
            <label for="total_days">Total Days (excluding Sundays)</label>
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
            if (!startDateInput.value || !endDateInput.value) {
                totalDaysInput.value = 0;
                return;
            }

            let startDate = new Date(startDateInput.value);
            let endDate = new Date(endDateInput.value);

            if (endDate < startDate) {
                totalDaysInput.value = 0;
                return;
            }

            let count = 0;
            const curDate = new Date(startDate.getTime());
            while (curDate <= endDate) {
                const dayOfWeek = curDate.getDay();
                if (dayOfWeek !== 0) { // 0 = Sunday
                    count++;
                }
                curDate.setDate(curDate.getDate() + 1);
            }
            totalDaysInput.value = count;
        }

        startDateInput.addEventListener('change', calculateDays);
        endDateInput.addEventListener('change', calculateDays);
        calculateDays(); // Initial calculation
    });
</script>
@endpush

