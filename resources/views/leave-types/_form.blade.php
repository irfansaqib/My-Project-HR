@csrf
<div class="form-group">
    <label for="name">Leave Type Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $leaveType->name ?? '') }}" required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Submit' }}</button>
<a href="{{ route('leave-types.index') }}" class="btn btn-secondary">Cancel</a>