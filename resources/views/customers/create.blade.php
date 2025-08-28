@extends('layouts.admin')

@section('title', 'Add New Customer')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Customer</h3>
    </div>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Individual" {{ old('type') == 'Individual' ? 'selected' : '' }}>Individual</option>
                        <option value="Partnership" {{ old('type') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                        <option value="Company" {{ old('type') == 'Company' ? 'selected' : '' }}>Company</option>
                    </select>
                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="cnic">CNIC / Registration Number <span class="text-danger">*</span></label>
                    <input type="text" name="cnic" class="form-control" id="cnic" value="{{ old('cnic') }}" required>
                    @error('cnic') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="contact_person">Contact Person (Optional)</label>
                    <input type="text" name="contact_person" class="form-control" id="contact_person" value="{{ old('contact_person') }}">
                    @error('contact_person') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <input type="text" name="phone" class="form-control" id="phone" value="{{ old('phone') }}">
                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="email">Email Address (Optional)</label>
                    <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="ntn">NTN No. (Optional)</label>
                    <input type="text" name="ntn" class="form-control" id="ntn" value="{{ old('ntn') }}">
                    @error('ntn') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="address">Address (Optional)</label>
                    <input type="text" name="address" class="form-control" id="address" value="{{ old('address') }}">
                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="status">Status (Optional)</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Customer</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection