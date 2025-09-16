@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 form-group">
        <label for="legal_name">Legal Name <span class="text-danger">*</span></label>
        <input type="text" name="legal_name" id="legal_name" class="form-control" value="{{ old('legal_name', $business->legal_name ?? '') }}" required>
    </div>
    <div class="col-md-6 form-group">
        <label for="business_name">Business Name <span class="text-danger">*</span></label>
        <input type="text" name="business_name" id="business_name" class="form-control" value="{{ old('business_name', $business->business_name ?? '') }}" required>
    </div>
</div>

<div class="row">
    {{-- ✅ DEFINITIVE FIX: Converted the text input to a dropdown select menu. --}}
    <div class="col-md-4 form-group">
        <label for="business_type">Business Type</label>
        <select name="business_type" id="business_type" class="form-control">
            <option value="">Select Type</option>
            @php
                $types = ['Sole Proprietorship', 'Partnership', 'Private Limited Company', 'Public Limited Company', 'NGO / Trust'];
            @endphp
            @foreach($types as $type)
                <option value="{{ $type }}" @if(old('business_type', $business->business_type ?? '') == $type) selected @endif>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label for="registration_number">Registration / CNIC No.</label>
        <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number', $business->registration_number ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label for="ntn_number">NTN Number</label>
        <input type="text" name="ntn_number" id="ntn_number" class="form-control" value="{{ old('ntn_number', $business->ntn_number ?? '') }}">
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $business->email ?? '') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="phone_number">Phone Number</label>
        <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $business->phone_number ?? '') }}">
    </div>
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

