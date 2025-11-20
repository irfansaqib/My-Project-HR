@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 form-group">
        <label for="legal_name">Legal Name <span class="text-danger">*</span></label>
        <input type="text" name="legal_name" id="legal_name" class="form-control"
               value="{{ old('legal_name', $business->legal_name ?? '') }}" required>
    </div>

    <div class="col-md-6 form-group">
        <label for="business_name">Business Name <span class="text-danger">*</span></label>
        <input type="text" name="business_name" id="business_name" class="form-control"
               value="{{ old('business_name', $business->business_name ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label for="business_type">Business Type</label>
        <select name="business_type" id="business_type" class="form-control">
            <option value="">Select Type</option>
            @php
                $types = [
                    'Sole Proprietorship',
                    'Partnership',
                    'Private Limited Company',
                    'Public Limited Company',
                    'NGO / Trust'
                ];
            @endphp
            @foreach($types as $type)
                <option value="{{ $type }}" @if(old('business_type', $business->business_type ?? '') == $type) selected @endif>
                    {{ $type }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 form-group">
        <label for="registration_number">Registration / CNIC No.</label>
        <input type="text" name="registration_number" id="registration_number" class="form-control"
               value="{{ old('registration_number', $business->registration_number ?? '') }}">
    </div>

    <div class="col-md-4 form-group">
        <label for="ntn_number">NTN Number</label>
        <input type="text" name="ntn_number" id="ntn_number" class="form-control"
               value="{{ old('ntn_number', $business->ntn_number ?? '') }}">
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" class="form-control"
               value="{{ old('email', $business->email ?? '') }}">
    </div>

    <div class="col-md-6 form-group">
        <label for="phone_number">Phone Number</label>
        <input type="text" name="phone_number" id="phone_number" class="form-control"
               value="{{ old('phone_number', $business->phone_number ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $business->address ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="logo">Company Logo</label>
    <div class="custom-file">
        <input type="file" name="logo" class="custom-file-input" id="logo" accept="image/*">
        <label class="custom-file-label" for="logo">Choose file</label>
    </div>

    @if(isset($business) && !empty($business->logo_path))
        <div class="mt-3 text-center">
            <p class="text-muted mb-1">Current Logo:</p>
            <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Current Logo"
                 style="max-height: 120px; border: 1px solid #ccc; padding: 5px; border-radius: 6px;">
        </div>
    @else
        <p class="text-muted mt-2 mb-0">No logo uploaded yet.</p>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('logo');
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const fileName = e.target.files.length ? e.target.files[0].name : 'Choose file';
                e.target.nextElementSibling.innerText = fileName;
            });
        }
    });
</script>
@endpush
