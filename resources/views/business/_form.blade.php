<div class="card-body">
    <div class="row">
        <div class="col-md-9">
            <h5 class="mb-3">Business Information</h5>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="business_name">Business Name <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control" id="business_name" value="{{ old('business_name', $business->business_name ?? '') }}" required>
                    @error('business_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="legal_name">Legal Name <span class="text-danger">*</span></label>
                    <input type="text" name="legal_name" class="form-control" id="legal_name" value="{{ old('legal_name', $business->legal_name ?? '') }}" required>
                    @error('legal_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="registration_number">Registration / CNIC No. <span class="text-danger">*</span></label>
                    <input type="text" name="registration_number" class="form-control" id="registration_number" value="{{ old('registration_number', $business->registration_number ?? '') }}" required>
                    @error('registration_number') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="ntn_number">NTN Number</label>
                    <input type="text" name="ntn_number" class="form-control" id="ntn_number" value="{{ old('ntn_number', $business->ntn_number ?? '') }}">
                    @error('ntn_number') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
             <div class="row">
                <div class="col-md-12 form-group">
                    {{-- ============================================================= --}}
                    {{-- === THIS SECTION IS NOW A DROPDOWN                         === --}}
                    {{-- ============================================================= --}}
                    <label for="business_type">Business Type <span class="text-danger">*</span></label>
                    <select name="business_type" id="business_type" class="form-control" required>
                        <option value="">-- Select a Type --</option>
                        <option value="Individual" {{ old('business_type', $business->business_type ?? '') == 'Individual' ? 'selected' : '' }}>Individual</option>
                        <option value="Partnership" {{ old('business_type', $business->business_type ?? '') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                        <option value="Company" {{ old('business_type', $business->business_type ?? '') == 'Company' ? 'selected' : '' }}>Company</option>
                    </select>
                    @error('business_type') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <label>Business Logo</label>
            <div class="mb-2">
                @if(isset($business) && $business->logo_path)
                    <img id="logo-preview" src="{{ asset('storage/' . $business->logo_path) }}" alt="Logo Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: contain;">
                @else
                    <img id="logo-preview" src="https://via.placeholder.com/150" alt="Logo Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: contain;">
                @endif
            </div>
            <div class="custom-file">
                <input type="file" name="logo" class="custom-file-input" id="logo" accept="image/*">
                <label class="custom-file-label" for="logo">Choose logo</label>
            </div>
            @error('logo') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr>
    <h5 class="mt-4 mb-3">Contact Details</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="phone_number">Contact Number <span class="text-danger">*</span></label>
            <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number', $business->phone_number ?? '') }}" required>
            @error('phone_number') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="email">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $business->email ?? '') }}" required>
            @error('email') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="address">Address <span class="text-danger">*</span></label>
        <textarea name="address" class="form-control" id="address" rows="3" required>{{ old('address', $business->address ?? '') }}</textarea>
        @error('address') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle file input label change
        $('.custom-file-input').on('change', function(event) {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Handle logo preview
        $('#logo').on('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    });
</script>
@endpush