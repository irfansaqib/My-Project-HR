<div class="card-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="business_name">Business Name</label>
                <input type="text" name="business_name" id="business_name" class="form-control @error('business_name') is-invalid @enderror"
                       value="{{ old('business_name', $business->business_name ?? '') }}" required>
                @error('business_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="legal_name">Legal Name</label>
                <input type="text" name="legal_name" id="legal_name" class="form-control @error('legal_name') is-invalid @enderror"
                       value="{{ old('legal_name', $business->legal_name ?? '') }}">
                @error('legal_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="registration_number">Registration Number</label>
                <input type="text" name="registration_number" id="registration_number" class="form-control @error('registration_number') is-invalid @enderror"
                       value="{{ old('registration_number', $business->registration_number ?? '') }}">
                @error('registration_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="ntn_number">NTN Number</label>
                <input type="text" name="ntn_number" id="ntn_number" class="form-control @error('ntn_number') is-invalid @enderror"
                       value="{{ old('ntn_number', $business->ntn_number ?? '') }}">
                @error('ntn_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone_number">Contact Phone</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                       value="{{ old('phone_number', $business->phone_number ?? '') }}">
                @error('phone_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Contact Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $business->email ?? '') }}">
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="business_type">Business Type</label>
        <input type="text" name="business_type" id="business_type" class="form-control @error('business_type') is-invalid @enderror"
               value="{{ old('business_type', $business->business_type ?? '') }}">
        @error('business_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $business->address ?? '') }}</textarea>
        @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="logo">Business Logo</label>
        <div class="custom-file">
            <input type="file" name="logo" class="custom-file-input @error('logo') is-invalid @enderror" id="logo">
            <label class="custom-file-label" for="logo">Choose file</label>
        </div>
        @error('logo')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
        @if(isset($business) && $business->logo_path)
            <div class="mt-2"><img src="{{ asset('storage/' . $business->logo_path) }}" alt="Current Logo" style="max-height: 100px;"></div>
        @endif
    </div>
</div>