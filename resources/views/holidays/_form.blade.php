@csrf
<div class="row">
    <div class="col-md-6 form-group">
        <label for="title">Holiday Title <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $holiday->title ?? '') }}" required>
        @error('title')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 form-group">
        <label for="date">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', isset($holiday) ? $holiday->date->format('Y-m-d') : '') }}" required>
        @error('date')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Submit' }}</button>
<a href="{{ route('holidays.index') }}" class="btn btn-secondary">Cancel</a>