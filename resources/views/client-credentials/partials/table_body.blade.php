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
            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this login detail?')">Delete</button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">No login details found.</td>
</tr>
@endforelse