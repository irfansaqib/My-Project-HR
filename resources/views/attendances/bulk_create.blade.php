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
                        <tr>
                            <td colspan="4" class="text-center text-muted">Select a date to load employees.</td>
                        </tr>
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

        tbody.on('input', '.check-in', function() {
            const checkInValue = $(this).val();
            const checkOutInput = $(this).closest('tr').find('.check-out');
            
            if (checkInValue) {
                checkOutInput.prop('disabled', false);
            } else {
                checkOutInput.prop('disabled', true);
                checkOutInput.val('');
            }
        });

        function fetchAttendanceData() {
            const selectedDate = dateInput.val();
            formDateInput.val(selectedDate);
            tbody.html(`<tr><td colspan="4" class="text-center">Loading...</td></tr>`);

            $.ajax({
                url: `{{ route('api.employees-for-attendance') }}`,
                type: 'GET',
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
                        
                        // ** NEW LOGIC TO DISABLE SAVED FIELDS **
                        const isReadOnly = attendance.id ? 'readonly' : '';
                        const isSelectDisabled = attendance.id ? 'disabled' : '';
                        const isCheckoutDisabled = !checkInValue || checkOutValue ? 'disabled' : '';

                        const row = `
                            <tr>
                                <td>
                                    ${employee.name}
                                    <input type="hidden" name="attendances[${index}][employee_id]" value="${employee.id}">
                                </td>
                                <td>
                                    <select name="attendances[${index}][status]" class="form-control status-select" ${isSelectDisabled}>
                                        <option value="present" ${attendance.status === 'present' ? 'selected' : ''}>Present</option>
                                        <option value="absent" ${!attendance.status || attendance.status === 'absent' ? 'selected' : ''}>Absent</option>
                                        <option value="leave" ${attendance.status === 'leave' ? 'selected' : ''}>Leave</option>
                                        <option value="half-day" ${attendance.status === 'half-day' ? 'selected' : ''}>Half-day</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="time" name="attendances[${index}][check_in]" class="form-control check-in" value="${checkInValue}" ${isReadOnly}>
                                </td>
                                <td>
                                    <input type="time" name="attendances[${index}][check_out]" class="form-control check-out" value="${checkOutValue}" ${isCheckoutDisabled}>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                },
                error: function(xhr) {
                    tbody.html(`<tr><td colspan="4" class="text-center text-danger">Failed to load data. Please check server logs for details.</td></tr>`);
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        dateInput.on('change', fetchAttendanceData);
        fetchAttendanceData();
    });
</script>
@endpush