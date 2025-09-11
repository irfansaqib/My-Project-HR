@csrf
<div class="row">
    <div class="col-md-6 form-group">
        <label for="name">Shift Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $shift->name ?? '') }}" required>
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 form-group">
        <label for="grace_period_in_minutes">Grace Period for Late Arrival (Minutes) <span class="text-danger">*</span></label>
        <input type="number" name="grace_period_in_minutes" id="grace_period_in_minutes" class="form-control @error('grace_period_in_minutes') is-invalid @enderror" value="{{ old('grace_period_in_minutes', $shift->grace_period_in_minutes ?? 10) }}" required>
        @error('grace_period_in_minutes')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">Shift Timings</h5>
        <div class="form-group">
            <label for="start_time">Start Time <span class="text-danger">*</span></label>
            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', optional($shift ?? null)->start_time ? $shift->start_time->format('H:i') : '') }}" required>
            @error('start_time')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="end_time">End Time <span class="text-danger">*</span></label>
            <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', optional($shift ?? null)->end_time ? $shift->end_time->format('H:i') : '') }}" required>
            @error('end_time')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <h5 class="mb-3">Punch Acceptance Window</h5>
        <div class="form-group">
            <label for="punch_in_window_start">Window Opens At <span class="text-danger">*</span></label>
            <input type="time" name="punch_in_window_start" id="punch_in_window_start" class="form-control @error('punch_in_window_start') is-invalid @enderror" value="{{ old('punch_in_window_start', optional($shift ?? null)->punch_in_window_start ? $shift->punch_in_window_start->format('H:i') : '') }}" required>
            @error('punch_in_window_start')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="punch_in_window_end">Window Closes At <span class="text-danger">*</span></label>
            <input type="time" name="punch_in_window_end" id="punch_in_window_end" class="form-control @error('punch_in_window_end') is-invalid @enderror" value="{{ old('punch_in_window_end', optional($shift ?? null)->punch_in_window_end ? $shift->punch_in_window_end->format('H:i') : '') }}" required>
            @error('punch_in_window_end')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12 form-group">
        <label for="weekly_off">Weekly Off Days</label>
        <input type="text" name="weekly_off" id="weekly_off" class="form-control @error('weekly_off') is-invalid @enderror" value="{{ old('weekly_off', $shift->weekly_off ?? 'Sunday') }}" placeholder="e.g., Sunday,Saturday">
        <small class="form-text text-muted">Enter off days separated by a comma (e.g., Sunday,Saturday).</small>
        @error('weekly_off')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Submit' }}</button>
<a href="{{ route('shifts.index') }}" class="btn btn-secondary">Cancel</a>