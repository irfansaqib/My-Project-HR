@extends('layouts.admin')

@section('title', 'Client Credentials')

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
        <h3 class="card-title">Client Credentials List</h3>
        <a href="{{ route('client-credentials.create') }}" class="btn btn-primary float-right">Add New Credential</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('client-credentials.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search any field..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-secondary" type="submit">Search</button>
                    <a href="{{ route('client-credentials.index') }}" class="btn btn-default">Clear</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>User Name</th>
                        <th>User ID</th>
                        <th>Password</th>
                        <th>PIN</th>
                        <th>Portal</th>
                        <th style="width: 180px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($credentials as $credential)
                    <tr>
                        <td>{{ $credential->company_name }}</td>
                        <td>{{ $credential->user_name }}</td>
                        <td>{{ $credential->login_id }}</td>
                        <td>{{ $credential->password }}</td>
                        <td>{{ $credential->pin }}</td>
                        <td>{{ $credential->portal_url }}</td>
                        <td>
                            <a href="{{ route('client-credentials.show', $credential) }}" class="btn btn-xs btn-info">View</a>
                            <a href="{{ route('client-credentials.edit', $credential) }}" class="btn btn-xs btn-warning">Edit</a>
                            <form method="POST" action="{{ route('client-credentials.destroy', $credential) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this credential?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No credentials found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection