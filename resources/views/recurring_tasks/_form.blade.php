{{-- REUSE STANDARD TASK FIELDS (Client, Employee, Category, Priority) --}}
@include('tasks._form', [
    'task' => $profile ?? null, 
    'clients' => $clients, 
    'employees' => $employees, 
    'categories' => $categories
])

<hr>
<h5 class="text-info font-weight-bold mb-3"><i class="fas fa-sync-alt mr-2"></i>Recurrence Rules</h5>

<div class="form-group">
    <label class="font-weight-bold">Frequency</label>
    <select name="frequency" id="freq" class="form-control" onchange="toggleFields()">
        @foreach(['Daily', 'Weekly', 'Fortnightly', 'Monthly', 'Quarterly', 'Annually'] as $f)
            <option value="{{ $f }}" {{ (old('frequency', $profile->frequency ?? '') == $f) ? 'selected' : '' }}>{{ $f }}</option>
        @endforeach
    </select>
</div>

{{-- 1. DAILY --}}
<div id="sec_daily" class="freq-section">
    <div class="row">
        <div class="col-md-6">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" 
                   value="{{ old('start_time', isset($profile->start_time) ? \Carbon\Carbon::parse($profile->start_time)->format('H:i') : '') }}">
        </div>
        <div class="col-md-6">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" 
                   value="{{ old('end_time', isset($profile->end_time) ? \Carbon\Carbon::parse($profile->end_time)->format('H:i') : '') }}">
        </div>
    </div>
</div>

{{-- 2. WEEKLY --}}
<div id="sec_weekly" class="freq-section" style="display:none;">
    <div class="row">
        <div class="col-md-6">
            <label>Run on Day</label>
            <select name="day_of_week" class="form-control">
                @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d)
                    <option value="{{$d}}" {{ (old('day_of_week', $profile->day_of_week ?? '') == $d) ? 'selected' : '' }}>{{$d}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label>Deadline Gap (Days)</label>
            <input type="number" name="duration_days" class="form-control" placeholder="e.g. 2" 
                   value="{{ old('duration_days', $profile->duration_days ?? '') }}">
        </div>
    </div>
</div>

{{-- 3. FORTNIGHTLY --}}
<div id="sec_fortnightly" class="freq-section" style="display:none;">
    <div class="row">
        <div class="col-md-6">
            <label>Reference Start Date</label>
            <input type="date" name="reference_start_date" class="form-control" 
                   value="{{ old('reference_start_date', isset($profile->reference_start_date) ? $profile->reference_start_date->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-6">
            <label>Deadline Gap (Days)</label>
            <input type="number" name="duration_days" class="form-control" 
                   value="{{ old('duration_days', $profile->duration_days ?? '') }}">
        </div>
    </div>
</div>

{{-- 4. MONTHLY --}}
<div id="sec_monthly" class="freq-section" style="display:none;">
    <div class="row">
        <div class="col-md-6">
            <label>Start Day (1-31)</label>
            <input type="number" name="month_start_day" class="form-control" min="1" max="31" 
                   value="{{ old('month_start_day', $profile->month_start_day ?? '') }}">
        </div>
        <div class="col-md-6">
            <label>End Day (1-31)</label>
            <input type="number" name="month_end_day" class="form-control" min="1" max="31" 
                   value="{{ old('month_end_day', $profile->month_end_day ?? '') }}">
        </div>
    </div>
</div>

{{-- 5. QUARTERLY --}}
<div id="sec_quarterly" class="freq-section" style="display:none;">
    <div class="row">
        <div class="col-md-6">
            <label>Start Date (of 1st Quarter)</label>
            <input type="date" name="reference_start_date" class="form-control" 
                   value="{{ old('reference_start_date', isset($profile->reference_start_date) ? $profile->reference_start_date->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-6">
            <label>End Date (of 1st Quarter)</label>
            <input type="date" name="annual_end_date" class="form-control" 
                   value="{{ old('annual_end_date', isset($profile->annual_end_date) ? $profile->annual_end_date->format('Y-m-d') : '') }}">
        </div>
    </div>
    <small class="text-muted">System will auto-calculate for subsequent quarters.</small>
</div>

{{-- 6. ANNUALLY --}}
<div id="sec_annually" class="freq-section" style="display:none;">
    <div class="row">
        <div class="col-md-6">
            <label>Annual Start Date</label>
            <input type="date" name="annual_start_date" class="form-control" 
                   value="{{ old('annual_start_date', isset($profile->annual_start_date) ? $profile->annual_start_date->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-6">
            <label>Annual End Date</label>
            <input type="date" name="annual_end_date" class="form-control" 
                   value="{{ old('annual_end_date', isset($profile->annual_end_date) ? $profile->annual_end_date->format('Y-m-d') : '') }}">
        </div>
    </div>
</div>

{{-- STATUS TOGGLE (For Edit) --}}
@if(isset($profile))
<div class="form-group mt-3">
    <label>Profile Status</label>
    <select name="status" class="form-control">
        <option value="Active" {{ $profile->status == 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ $profile->status == 'Inactive' ? 'selected' : '' }}>Inactive (Paused)</option>
    </select>
</div>
@endif

<div class="text-right mt-4">
    <a href="{{ route('recurring-tasks.index') }}" class="btn btn-secondary">Cancel</a>
    <button class="btn btn-success px-5 font-weight-bold">
        <i class="fas fa-save mr-1"></i> {{ isset($profile) ? 'Update Profile' : 'Save Profile' }}
    </button>
</div>

<script>
    function toggleFields() {
        document.querySelectorAll('.freq-section').forEach(el => el.style.display = 'none');
        let val = document.getElementById('freq').value.toLowerCase();
        let target = document.getElementById('sec_' + val);
        if(target) target.style.display = 'block';
    }
    // Run on load
    document.addEventListener("DOMContentLoaded", function() { toggleFields(); });
</script>