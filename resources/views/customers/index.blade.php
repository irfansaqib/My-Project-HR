@extends('layouts.admin')

@section('title', 'Customers')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Customer List</h3>
        <a href="{{ route('customers.create') }}" class="btn btn-primary float-right">Add New Customer</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>CNIC / Reg. No.</th>
                        <th>NTN</th>
                        <th style="width: 180px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->customer_id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->type }}</td>
                        <td>{{ $customer->cnic }}</td>
                        <td>{{ $customer->ntn }}</td>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-info">View</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-warning">Edit</a>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection