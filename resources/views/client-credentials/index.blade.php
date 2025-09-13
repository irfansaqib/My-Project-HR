@extends('layouts.admin')

@section('title', 'Client Credentials')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Client Credentials List</h3>
        <a href="{{ route('client-credentials.create') }}" class="btn btn-primary float-right">Add New Credential</a>
    </div>
    <div class="card-body">
        <form id="search-form" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Search by Company Name..." value="{{ request('company_name') }}">
                </div>
                <div class="col-md-4">
                    <input type="text" name="user_name" id="user_name" class="form-control" placeholder="Search by User Name..." value="{{ request('user_name') }}">
                </div>
                <div class="col-md-4">
                    <input type="text" name="portal_url" id="portal_url" class="form-control" placeholder="Search by Portal..." value="{{ request('portal_url') }}">
                </div>
            </div>
        </form>

        <div id="credentials-table-container">
            @include('client-credentials._credentials_table')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;

    function fetchCredentials() {
        // Show a loading indicator (optional but good for UX)
        $('#credentials-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');

        $.ajax({
            url: "{{ route('client-credentials.index') }}",
            type: 'GET',
            data: $('#search-form').serialize(),
            success: function(data) {
                $('#credentials-table-container').html(data);
            },
            error: function() {
                // Handle errors, e.g., show a message
                $('#credentials-table-container').html('<p class="text-center text-danger">Failed to load credentials.</p>');
            }
        });
    }

    // Use 'keyup' for instant feedback as user types
    $('#search-form input').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetchCredentials();
        }, 300); // 300ms delay to avoid excessive requests while typing
    });

    // Handle pagination clicks via AJAX
    $(document).on('click', '#credentials-table-container .pagination a', function(event) {
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        var url = "{{ route('client-credentials.index') }}?" + $('#search-form').serialize() + "&page=" + page;

        $('#credentials-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#credentials-table-container').html(data);
            },
            error: function() {
                $('#credentials-table-container').html('<p class="text-center text-danger">Failed to load credentials.</p>');
            }
        });
    });
});
</script>
@endpush

