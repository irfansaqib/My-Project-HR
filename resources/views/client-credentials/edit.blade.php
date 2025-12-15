@extends('layouts.admin')

@section('title', 'Edit Login Detail')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Login Detail</h3>
    </div>
    <form method="POST" action="{{ route('client-credentials.update', $credential) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" name="company_name" class="form-control" id="company_name" value="{{ old('company_name', $credential->company_name) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="portal_url">Portal</label>
                    <input type="text" name="portal_url" class="form-control" id="portal_url" value="{{ old('portal_url', $credential->portal_url) }}" required>
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Authentication Details</h5>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="user_name">User Name</label>
                    <input type="text" name="user_name" class="form-control" id="user_name" value="{{ old('user_name', $credential->user_name) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="login_id">User ID / Login ID</label>
                    <input type="text" name="login_id" class="form-control" id="login_id" value="{{ old('login_id', $credential->login_id) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="password">User Password</label>
                    <input type="text" name="password" class="form-control" id="password" value="{{ old('password', $credential->password) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="pin">PIN</label>
                    <input type="text" name="pin" class="form-control" id="pin" value="{{ old('pin', $credential->pin) }}">
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Contact Information (Optional)</h5>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="address">Address</label>
                    <textarea name="address" class="form-control" id="address" rows="3">{{ old('address', $credential->address) }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $credential->email) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label for="company_email">Company Email</label>
                    <input type="email" name="company_email" class="form-control" id="company_email" value="{{ old('company_email', $credential->company_email) }}">
                </div>
            </div>
            <div class="row">
                 <div class="col-md-6 form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" id="contact_number" value="{{ old('contact_number', $credential->contact_number) }}">
                </div>
            </div>

            <hr>
            <h5 class="mt-4 mb-3">Director & CEO Info (Optional)</h5>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="director_email">Email Address of Director</label>
                    <input type="email" name="director_email" class="form-control" id="director_email" value="{{ old('director_email', $credential->director_email) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label for="director_email_password">Director Email Password</label>
                    <input type="text" name="director_email_password" class="form-control" id="director_email_password" value="{{ old('director_email_password', $credential->director_email_password) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="ceo_name">CEO Name</label>
                    <input type="text" name="ceo_name" class="form-control" id="ceo_name" value="{{ old('ceo_name', $credential->ceo_name) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label for="ceo_cnic">CEO CNIC</label>
                    <input type="text" name="ceo_cnic" class="form-control" id="ceo_cnic" value="{{ old('ceo_cnic', $credential->ceo_cnic) }}">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Login Detail</button>
            <a href="{{ route('client-credentials.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection