@extends('layouts.admin')

@section('title', 'Add Business Details')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add Your Business Details</h3>
    </div>
    <form method="POST" action="{{ route('business.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="legal_name">Legal Name</label>
                        <input type="text" name="legal_name" class="form-control" id="legal_name" placeholder="Enter Legal Name" value="{{ old('legal_name') }}" required>
                        @error('legal_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input type="text" name="business_name" class="form-control" id="business_name" placeholder="Enter Business Name" value="{{ old('business_name') }}" required>
                        @error('business_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="business_type">Business Type</label>
                        <select name="business_type" class="form-control" id="business_type" required>
                            <option value="">Select Type</option>
                            <option value="Individual" {{ old('business_type') == 'Individual' ? 'selected' : '' }}>Individual</option>
                            <option value="Sole Proprietorship" {{ old('business_type') == 'Sole Proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                            <option value="Partnership" {{ old('business_type') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                            <option value="Private Limited Company" {{ old('business_type') == 'Private Limited Company' ? 'selected' : '' }}>Private Limited Company</option>
                        </select>
                        @error('business_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ntn_number">NTN No.</label>
                        <input type="text" name="ntn_number" class="form-control" id="ntn_number" placeholder="Enter NTN Number" value="{{ old('ntn_number') }}">
                        @error('ntn_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="registration_number">CNIC No. / Incorporation Number</label>
                <input type="text" name="registration_number" class="form-control" id="registration_number" placeholder="Enter 13-digit CNIC or Incorporation Number" value="{{ old('registration_number') }}" required>
                @error('registration_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" class="form-control" id="address" rows="3" placeholder="Enter full address" required>{{ old('address') }}</textarea>
                @error('address') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="e.g., 03001234567" value="{{ old('phone_number') }}" required>
                        @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="Enter contact email" value="{{ old('email') }}" required>
                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="logo">Add Logo (.PNG)</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="logo" class="custom-file-input" id="logo">
                        <label class="custom-file-label" for="logo">Choose file</label>
                    </div>
                </div>
                @error('logo') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Details</button>
        </div>
    </form>
</div>
@endsection