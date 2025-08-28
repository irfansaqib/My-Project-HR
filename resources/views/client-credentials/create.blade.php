@extends('layouts.admin')

@section('title', 'Add New Credential')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Client Credential</h3>
    </div>
    <form method="POST" action="{{ route('client-credentials.store') }}">
        @csrf
        <div class="card-body">

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" name="company_name" class="form-control" id="company_name" value="{{ old('company_name') }}" required autocomplete="off">
                    @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="portal_url">Portal</label>
                    <input type="text" name="portal_url" class="form-control" id="portal_url" value="{{ old('portal_url') }}" required placeholder="Enter Portal Address or Name" autocomplete="off">
                    @error('portal_url') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Login Details</h5>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="user_name">User Name</label>
                    <input type="text" name="user_name" class="form-control" id="user_name" value="{{ old('user_name') }}" required autocomplete="off">
                    @error('user_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="login_id">User ID / Login ID</label>
                    <input type="text" name="login_id" class="form-control" id="login_id" value="{{ old('login_id') }}" required autocomplete="off">
                    @error('login_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="password">User Password</label>
                    <input type="text" name="password" class="form-control" id="password" required autocomplete="off">
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="pin">PIN</label>
                    <input type="text" name="pin" class="form-control" id="pin" value="{{ old('pin') }}" autocomplete="off">
                    @error('pin') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Contact Information (Optional)</h5>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="address">Address</label>
                    <textarea name="address" class="form-control" id="address" rows="3" autocomplete="off">{{ old('address') }}</textarea>
                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" autocomplete="off">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="company_email">Company Email</label>
                    <input type="email" name="company_email" class="form-control" id="company_email" value="{{ old('company_email') }}" autocomplete="off">
                    @error('company_email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row">
                 <div class="col-md-6 form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" id="contact_number" value="{{ old('contact_number') }}" autocomplete="off">
                    @error('contact_number') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Director & CEO Info (Optional)</h5>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="director_email">Email Address of Director</label>
                    <input type="email" name="director_email" class="form-control" id="director_email" value="{{ old('director_email') }}" autocomplete="off">
                    @error('director_email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="director_email_password">Director Email Password</label>
                    <input type="text" name="director_email_password" class="form-control" id="director_email_password" autocomplete="off">
                    @error('director_email_password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="ceo_name">CEO Name</label>
                    <input type="text" name="ceo_name" class="form-control" id="ceo_name" value="{{ old('ceo_name') }}" autocomplete="off">
                    @error('ceo_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="ceo_cnic">CEO CNIC</label>
                    <input type="text" name="ceo_cnic" class="form-control" id="ceo_cnic" value="{{ old('ceo_cnic') }}" autocomplete="off">
                    @error('ceo_cnic') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Credential</button>
            <a href="{{ route('client-credentials.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection