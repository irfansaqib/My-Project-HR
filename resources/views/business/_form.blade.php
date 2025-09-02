@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="name">Business Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $business->name ?? '') }}" required>
</div>

<div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $business->email ?? '') }}">
</div>

<div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $business->phone ?? '') }}">
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $business->address ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="logo">Company Logo</label>
    <div class="custom-file">
        <input type="file" name="logo" class="custom-file-input" id="logo">
        <label class="custom-file-label" for="logo">Choose file</label>
    </div>
    @if(isset($business) && $business->logo_path)
        <div class="mt-2">
            <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Current Logo" style="max-height: 100px;">
        </div>
    @endif
</div>