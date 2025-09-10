@extends('layouts.admin')
@section('title', 'Mark Attendance')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mark Manual Attendance</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="employee_id">Select Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-control selectpicker" data-live-search="true" required>
                        <option value="">-- Select an Employee --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_number }} | {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="date">Attendance Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="check_in">Check In Time <span class="text-danger">*</span></label>
                    <input type="time" name="check_in" id="check_in" class="form-control" value="{{ old('check_in') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="check_out">Check Out Time <span class="text-danger">*</span></label>
                    <input type="time" name="check_out" id="check_out" class="form-control" value="{{ old('check_out') }}" required>
                </div>
            </div>
            <div class="row">
                 <div class="col-md-6 form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="leave" {{ old('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                        <option value="half-day" {{ old('status') == 'half-day' ? 'selected' : '' }}>Half-day</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Attendance</button>
            <a href="{{ route('attendances.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
@endpush