@extends('layouts.admin')

@section('title', 'Edit Business Details')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Business Details</h3>
    </div>
    <form method="POST" action="{{ route('business.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="legal_name">Legal Name</label>
                        <input type="text" name="legal_name" class="form-control" id="legal_name" value="{{ old('legal_name', $business->legal_name) }}" required>
                        @error('legal_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input type="text" name="business_name" class="form-control" id="business_name" value="{{ old('business_name', $business->business_name) }}" required>
                        @error('business_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="business_type">Business Type</label>
                        <select name="business_type" class="form-control" id="business_type" required>
                            <option value="Individual" {{ old('business_type', $business->business_type) == 'Individual' ? 'selected' : '' }}>Individual</option>
                            <option value="Sole Proprietorship" {{ old('business_type', $business->business_type) == 'Sole Proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                            <option value="Partnership" {{ old('business_type', $business->business_type) == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                            <option value="Private Limited Company" {{ old('business_type', $business->business_type) == 'Private Limited Company' ? 'selected' : '' }}>Private Limited Company</option>
                        </select>
                        @error('business_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ntn_number">NTN No.</label>
                        <input type="text" name="ntn_number" class="form-control" id="ntn_number" value="{{ old('ntn_number', $business->ntn_number) }}">
                        @error('ntn_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="registration_number">CNIC No. / Incorporation Number</label>
                <input type="text" name="registration_number" class="form-control" id="registration_number" value="{{ old('registration_number', $business->registration_number) }}" required>
                @error('registration_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" class="form-control" id="address" rows="3" required>{{ old('address', $business->address) }}</textarea>
                @error('address') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number', $business->phone_number) }}" required>
                        @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $business->email) }}" required>
                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="logo">Update Logo (.PNG) - Optional</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="logo" class="custom-file-input" id="logo">
                        <label class="custom-file-label" for="logo">Choose new file</label>
                    </div>
                </div>
                @error('logo') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Details</button>
            <a href="{{ route('business.show') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection