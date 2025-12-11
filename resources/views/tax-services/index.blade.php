@extends('layouts.admin')
@section('title', 'Tax Services Clients')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-briefcase mr-2 text-primary"></i> Tax Services Clients</h4>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addClientModal">
            <i class="fas fa-plus-circle mr-1"></i> Add New Client
        </button>
    </div>

    <div class="row">
        @foreach($clients as $client)
        <div class="col-md-4 col-sm-6">
            <div class="card card-outline card-primary h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">{{ $client->name }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-light border">{{ $client->employees_count }} Employees</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="fas fa-id-card mr-1"></i> NTN: {{ $client->ntn ?? 'N/A' }}</p>
                    <p class="text-muted small mb-3"><i class="fas fa-user mr-1"></i> Contact: {{ $client->contact_person ?? 'N/A' }}</p>
                    
                    <a href="{{ route('tax-services.clients.show', $client->id) }}" class="btn btn-block btn-outline-primary btn-sm">
                        Manage Payroll <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add Client Modal --}}
    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New Tax Client</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('tax-services.clients.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>NTN / Reg No</label>
                            <input type="text" name="ntn" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection