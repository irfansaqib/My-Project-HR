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
                    <form method="POST" action="{{ route('client-credentials.destroy', $credential) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this credential?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No credentials found matching your criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $credentials->links() }}
</div>

