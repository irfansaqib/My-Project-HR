@extends('layouts.admin')

@section('title', 'View Customer')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Details for {{ $customer->name }}</h3>
        <div class="card-tools">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">Edit Customer</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Primary Information</h4>
                <hr>
                <dl class="row">
                    <dt class="col-sm-5">Customer ID</dt>
                    <dd class="col-sm-7">{{ $customer->customer_id }}</dd>

                    <dt class="col-sm-5">Customer Name</dt>
                    <dd class="col-sm-7">{{ $customer->name }}</dd>

                    <dt class="col-sm-5">Type</dt>
                    <dd class="col-sm-7">{{ $customer->type }}</dd>

                    <dt class="col-sm-5">CNIC / Registration No.</dt>
                    <dd class="col-sm-7">{{ $customer->cnic }}</dd>

                    <dt class="col-sm-5">NTN No.</dt>
                    <dd class="col-sm-7">{{ $customer->ntn ?? 'N/A' }}</dd>

                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7"><span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($customer->status) }}</span></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <h4>Contact Information</h4>
                <hr>
                <dl class="row">
                    <dt class="col-sm-5">Contact Person</dt>
                    <dd class="col-sm-7">{{ $customer->contact_person ?? 'N/A' }}</dd>

                    <dt class="col-sm-5">Phone Number</dt>
                    <dd class="col-sm-7">{{ $customer->phone ?? 'N/A' }}</dd>

                    <dt class="col-sm-5">Email Address</dt>
                    <dd class="col-sm-7">{{ $customer->email ?? 'N/A' }}</dd>

                    <dt class="col-sm-5">Address</dt>
                    <dd class="col-sm-7">{{ $customer->address ?? 'N/A' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection