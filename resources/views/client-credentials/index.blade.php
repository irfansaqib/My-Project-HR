@extends('layouts.admin')

@section('title', 'Login Details')

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
        <h3 class="card-title">Login Details List</h3>
        <a href="{{ route('client-credentials.create') }}" class="btn btn-primary float-right">Add New Login Detail</a>
    </div>
    <div class="card-body">
        {{-- Search Input Area --}}
        <div class="mb-4">
            <div class="input-group">
                {{-- Added ID 'live-search' --}}
                <input type="text" id="live-search" name="search" class="form-control" placeholder="Type to search login details..." value="{{ request('search') }}" autocomplete="off">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
        </div>

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
                {{-- Added ID 'table-body' to target with JS --}}
                <tbody id="table-body">
                    {{-- Include the partial we created --}}
                    @include('client-credentials.partials.table_body')
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- JavaScript for Live Search --}}
@push('scripts')
<script>
    $(document).ready(function() {
        let timeout = null;

        $('#live-search').on('keyup', function() {
            clearTimeout(timeout); // Clear the previous timer
            let query = $(this).val();

            // Delay search by 300ms to avoid requests on every single keystroke
            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('client-credentials.index') }}",
                    type: "GET",
                    data: { search: query },
                    success: function(data) {
                        $('#table-body').html(data);
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                    }
                });
            }, 300);
        });
    });
</script>
@endpush

@endsection