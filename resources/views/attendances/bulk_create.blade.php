@extends('layouts.admin')
@section('title', 'Bulk Mark Attendance')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bulk Attendance Sheet</h3>
    </div>
    <div class="card-body">
        {{-- ✅ MODIFIED: Added a new row for the Shift dropdown --}}
        <div class="form-row align-items-center mb-3">
            <div class="col-md-4">
                <label for="date">Select Date:</label>
                <input type="date" id="date" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label for="shift_id">Select Shift (Optional):</label>
                <select id="shift_id" class="form-control">
                    <option value="">All Employees</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">
                            {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <form action="{{ route('attendances.bulk.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" id="form_date" value="{{ now()->format('Y-m-d') }}">
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody">
                        <tr><td colspan="4" class="text-center text-muted">Select a date to load employees.</td></tr>
                    </tbody>
                </table>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Save Attendance Sheet</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        const dateInput = $('#date');
        const shiftInput = $('#shift_id'); // Get the new shift dropdown
        const formDateInput = $('#form_date');
        const tbody = $('#attendance-tbody');

        function toggleRowInputs(row) {
            const status = row.find('.status-select').val();
            const checkInInput = row.find('.check-in');
            const checkOutInput = row.find('.check-out');

            if (status === 'leave' || status === 'absent') {
                checkInInput.prop('disabled', true).val('');
                checkOutInput.prop('disabled', true).val('');
            } else {
                checkInInput.prop('disabled', false);
                checkOutInput.prop('disabled', !checkInInput.val());
                 if (!checkInInput.val()) {
                    checkOutInput.val('');
                }
            }
        }

        tbody.on('change', '.status-select', function() {
            toggleRowInputs($(this).closest('tr'));
        });

        tbody.on('input', '.check-in', function() {
            toggleRowInputs($(this).closest('tr'));
        });

        function fetchAttendanceData() {
            const selectedDate = dateInput.val();
            const selectedShiftId = shiftInput.val(); // Get the selected shift ID
            formDateInput.val(selectedDate);
            tbody.html(`<tr><td colspan="4" class="text-center">Loading...</td></tr>`);

            // ✅ MODIFIED: Pass both date and shift_id to the API endpoint
            $.ajax({
                url: `{{ route('api.employees-for-attendance') }}`,
                data: { 
                    date: selectedDate,
                    shift_id: selectedShiftId 
                },
                success: function(employees) {
                    tbody.html('');
                    if (employees.length === 0) {
                        tbody.html(`<tr><td colspan="4" class="text-center text-muted">No active employees found for this date/shift.</td></tr>`);
                        return;
                    }
                    
                    employees.forEach(function(employee, index) {
                        const attendance = employee.attendances.length > 0 ? employee.attendances[0] : {};
                        const checkInValue = attendance.check_in ? attendance.check_in.substring(0, 5) : '';
                        const checkOutValue = attendance.check_out ? attendance.check_out.substring(0, 5) : '';
                        const status = attendance.status || 'present'; // Default to present for bulk marking

                        const row = `
                            <tr>
                                <td>
                                    ${employee.name}
                                    <input type="hidden" name="attendances[${index}][employee_id]" value="${employee.id}">
                                </td>
                                <td>
                                    <select name="attendances[${index}][status]" class="form-control status-select">
                                        <option value="present" ${status === 'present' ? 'selected' : ''}>Present</option>
                                        <option value="absent" ${status === 'absent' ? 'selected' : ''}>Absent</option>
                                        <option value="leave" ${status === 'leave' ? 'selected' : ''}>Leave</option>
                                        <option value="half-day" ${status === 'half-day' ? 'selected' : ''}>Half-day</option>
                                    </select>
                                </td>
                                <td><input type="time" name="attendances[${index}][check_in]" class="form-control check-in" value="${checkInValue}"></td>
                                <td><input type="time" name="attendances[${index}][check_out]" class="form-control check-out" value="${checkOutValue}"></td>
                            </tr>
                        `;
                        tbody.append(row);
                        toggleRowInputs(tbody.find('tr').last());
                    });
                }
            });
        }

        // ✅ MODIFIED: Trigger the fetch when either the date or the shift changes
        dateInput.on('change', fetchAttendanceData);
        shiftInput.on('change', fetchAttendanceData);
        
        // Initial load on page ready
        fetchAttendanceData();
    });
</script>
@endpush