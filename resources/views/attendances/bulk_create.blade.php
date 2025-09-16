@extends('layouts.admin')
@section('title', 'Bulk Mark Attendance')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bulk Attendance Sheet</h3>
    </div>
    <div class="card-body">
        <div class="form-group row align-items-center">
            <label for="date" class="col-sm-2 col-form-label">Select Date:</label>
            <div class="col-sm-4">
                <input type="date" id="date" class="form-control" value="{{ now()->format('Y-m-d') }}">
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
            formDateInput.val(selectedDate);
            tbody.html(`<tr><td colspan="4" class="text-center">Loading...</td></tr>`);

            $.ajax({
                url: `{{ route('api.employees-for-attendance') }}`,
                data: { date: selectedDate },
                success: function(employees) {
                    tbody.html('');
                    if (employees.length === 0) {
                        tbody.html(`<tr><td colspan="4" class="text-center text-muted">No active employees found.</td></tr>`);
                        return;
                    }
                    
                    employees.forEach(function(employee, index) {
                        const attendance = employee.attendances.length > 0 ? employee.attendances[0] : {};
                        const checkInValue = attendance.check_in ? attendance.check_in.substring(0, 5) : '';
                        const checkOutValue = attendance.check_out ? attendance.check_out.substring(0, 5) : '';
                        const status = attendance.status || 'absent';

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

        dateInput.on('change', fetchAttendanceData);
        fetchAttendanceData();
    });
</script>
@endpush

