@extends('layouts.admin')
@section('title', 'Assign Employee Shifts')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Assign Shifts to Employees</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('shift-assignments.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="employee_ids">Select Employees <span class="text-danger">*</span></label>
                    <select name="employee_ids[]" id="employee_ids" class="form-control selectpicker" data-live-search="true" multiple required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->employee_number }} | {{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="shift_id">Assign to Shift <span class="text-danger">*</span></label>
                    <select name="shift_id" id="shift_id" class="form-control" required>
                        <option value="">-- Select a Shift --</option>
                        @foreach($shifts as $shift)
                            {{-- ✅ DEFINITIVE FIX: Changed 'shift_name' to 'name' to correctly display the shift name. --}}
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="end_date">End Date (Optional)</label>
                    <input type="date" name="end_date" id="end_date" class="form-control">
                    <small class="text-muted">Leave blank for an ongoing assignment.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Assign Shifts</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.selectpicker').selectpicker();
        });
    </script>
@endpush
