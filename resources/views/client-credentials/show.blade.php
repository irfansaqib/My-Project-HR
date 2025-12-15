@extends('layouts.admin')

@section('title', 'View Login Detail')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Details for {{ $credential->company_name }}</h3>
        <div class="card-tools">
            <a href="{{ route('client-credentials.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('client-credentials.edit', $credential) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Authentication Details</h4>
                <hr>
                <dl class="row">
                    <dt class="col-sm-4">Portal</dt>
                    <dd class="col-sm-8">{{ $credential->portal_url }}</dd>

                    <dt class="col-sm-4">User Name</dt>
                    <dd class="col-sm-8">{{ $credential->user_name }}</dd>

                    <dt class="col-sm-4">User ID</dt>
                    <dd class="col-sm-8">{{ $credential->login_id }}</dd>

                    <dt class="col-sm-4">Password</dt>
                    <dd class="col-sm-8">{{ $credential->password }}</dd>

                    <dt class="col-sm-4">PIN</dt>
                    <dd class="col-sm-8">{{ $credential->pin ?? 'N/A' }}</dd>
                </dl>

                <h4 class="mt-4">Director & CEO Info</h4>
                <hr>
                <dl class="row">
                    <dt class="col-sm-4">Director Email</dt>
                    <dd class="col-sm-8">{{ $credential->director_email ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Director Email Password</dt>
                    <dd class="col-sm-8">{{ $credential->director_email_password ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">CEO Name</dt>
                    <dd class="col-sm-8">{{ $credential->ceo_name ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">CEO CNIC</dt>
                    <dd class="col-sm-8">{{ $credential->ceo_cnic ?? 'N/A' }}</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <h4>Contact Information</h4>
                <hr>
                <dl class="row">
                    <dt class="col-sm-4">Address</dt>
                    <dd class="col-sm-8">{{ $credential->address ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $credential->email ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Company Email</dt>
                    <dd class="col-sm-8">{{ $credential->company_email ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Contact Number</dt>
                    <dd class="col-sm-8">{{ $credential->contact_number ?? 'N/A' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection