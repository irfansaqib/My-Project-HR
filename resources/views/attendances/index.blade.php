@extends('layouts.admin')
@section('title', 'Daily Attendance')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daily Attendance Sheet</h3>
        <div>
            <a href="{{ route('attendances.create') }}" class="btn btn-success">Mark Single</a>
            <a href="{{ route('attendances.bulk.create') }}" class="btn btn-primary">Bulk Mark Attendance</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('attendances.index') }}" method="GET" class="form-inline mb-3">
            <div class="form-group mr-2">
                <label for="date" class="mr-2">Select Date:</label>
                {{-- ✅ FIX: Handles an empty/null date correctly --}}
                <input type="date" name="date" id="date" class="form-control" value="{{ $filterDate ?? '' }}">
            </div>
            <div class="form-group mr-2">
                <label for="shift_id" class="mr-2">Select Shift:</label>
                <select name="shift_id" id="shift_id" class="form-control">
                    <option value="">All Shifts</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected($filterShiftId == $shift->id)>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-info">View Report</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Hours</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr class="attendance-row" data-update-url="{{ route('attendances.update', $attendance->id) }}">
                            <td>{{ $attendance->date->format('d-M-Y') }}</td>
                            <td>{{ $attendance->employee->name }}</td>
                            <td>
                                <select name="status" class="form-control form-control-sm status-select" disabled>
                                    <option value="present" @selected($attendance->status == 'present')>Present</option>
                                    <option value="absent" @selected($attendance->status == 'absent')>Absent</option>
                                    <option value="leave" @selected($attendance->status == 'leave')>Leave</option>
                                    <option value="half-day" @selected($attendance->status == 'half-day')>Half-day</option>
                                </select>
                            </td>
                            <td><input type="time" name="check_in" class="form-control form-control-sm check-in" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}" disabled></td>
                            <td><input type="time" name="check_out" class="form-control form-control-sm check-out" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}" disabled></td>
                            <td class="work-duration">{!! $attendance->work_duration !!}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-btn">Edit</button>
                                <button type="button" class="btn btn-sm btn-success save-btn" style="display: none;">Save</button>
                                <button type="button" class="btn btn-sm btn-secondary cancel-btn" style="display: none;">Cancel</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No attendance records found for this date/shift.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $attendances->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- The JavaScript for this page remains unchanged --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('table tbody');

    let originalValues = {};

    tableBody.addEventListener('click', function (e) {
        const target = e.target;
        const row = target.closest('tr');
        if (!row) return;

        // --- EDIT BUTTON ---
        if (target.classList.contains('edit-btn')) {
            const inputs = row.querySelectorAll('select, input[type="time"]');
            
            originalValues[row.dataset.updateUrl] = {
                status: row.querySelector('.status-select').value,
                check_in: row.querySelector('.check-in').value,
                check_out: row.querySelector('.check-out').value,
            };

            inputs.forEach(input => input.disabled = false);
            target.style.display = 'none';
            row.querySelector('.save-btn').style.display = 'inline-block';
            row.querySelector('.cancel-btn').style.display = 'inline-block';
        }

        // --- CANCEL BUTTON ---
        if (target.classList.contains('cancel-btn')) {
            const originalData = originalValues[row.dataset.updateUrl];
            if (originalData) {
                row.querySelector('.status-select').value = originalData.status;
                row.querySelector('.check-in').value = originalData.check_in;
                row.querySelector('.check-out').value = originalData.check_out;
            }

            row.querySelectorAll('select, input[type="time"]').forEach(input => input.disabled = true);
            target.style.display = 'none';
            row.querySelector('.save-btn').style.display = 'none';
            row.querySelector('.edit-btn').style.display = 'inline-block';
        }

        // --- SAVE BUTTON ---
        if (target.classList.contains('save-btn')) {
            const url = row.dataset.updateUrl;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const formData = new FormData();
            formData.append('status', row.querySelector('.status-select').value);
            formData.append('check_in', row.querySelector('.check-in').value);
            formData.append('check_out', row.querySelector('.check-out').value);
            formData.append('_method', 'PATCH');

            target.textContent = 'Saving...';
            target.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if(data.attendance && data.attendance.work_duration) {
                        row.querySelector('.work-duration').innerHTML = data.attendance.work_duration;
                    }
                    
                    row.querySelectorAll('select, input[type="time"]').forEach(input => input.disabled = true);
                    target.style.display = 'none';
                    row.querySelector('.cancel-btn').style.display = 'none';
                    row.querySelector('.edit-btn').style.display = 'inline-block';
                }
            })
            .catch(error => {
                console.error('Update failed:', error);
                alert('An error occurred. Check the console for details.');
                const cancelButton = row.querySelector('.cancel-btn');
                if(cancelButton) cancelButton.click();
            })
            .finally(() => {
                target.textContent = 'Save';
                target.disabled = false;
            });
        }
    });
});
</script>
@endpush