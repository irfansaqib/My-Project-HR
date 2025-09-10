@extends('layouts.admin')
@section('title', 'Daily Attendance')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daily Attendance Sheet</h3>
        <a href="{{ route('attendances.bulk.create') }}" class="btn btn-primary">Bulk Mark Attendance</a>
    </div>
    <div class="card-body">
        <form action="{{ route('attendances.index') }}" method="GET" class="form-inline mb-3">
            <div class="form-group">
                <label for="date" class="mr-2">Select Date:</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $filterDate }}">
            </div>
            <button type="submit" class="btn btn-info ml-2">View Report</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Hours</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <form action="{{ route('attendances.update', $attendance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <tr class="attendance-row">
                            <td>{{ $attendance->employee->name }}</td>
                            <td>
                                <select name="status" class="form-control form-control-sm status-select" disabled>
                                    <option value="present" @if($attendance->status == 'present') selected @endif>Present</option>
                                    <option value="absent" @if($attendance->status == 'absent') selected @endif>Absent</option>
                                    <option value="leave" @if($attendance->status == 'leave') selected @endif>Leave</option>
                                    <option value="half-day" @if($attendance->status == 'half-day') selected @endif>Half-day</option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="check_in" class="form-control form-control-sm check-in" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}" disabled>
                            </td>
                            <td>
                                <input type="time" name="check_out" class="form-control form-control-sm check-out" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}" disabled>
                            </td>
                            <td>{{ $attendance->work_duration }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-btn">Edit</button>
                                <button type="submit" class="btn btn-sm btn-success save-btn" style="display: none;">Save</button>
                            </td>
                        </tr>
                        </form>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No attendance records found for this date.</td>
                        </tr>
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
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            const row = $(this).closest('.attendance-row');
            row.find('input, select').prop('disabled', false);
            $(this).hide();
            row.find('.save-btn').show();
        });
    });
</script>
@endpush