@forelse($employees as $employee)
<tr>
    <td>{{ $employee->employee_number }}</td>
    <td>{{ $employee->name }}</td>
    <td>{{ $employee->email }}</td>
    <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-Y') : 'N/A' }}</td>
    <td>{{ $employee->department->name ?? 'N/A' }}</td>
    <td>{{ $employee->designation->title ?? 'N/A' }}</td>
    <td>
        <span class="badge badge-{{ $employee->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($employee->status) }}</span>
    </td>
    <td>
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-xs btn-info">View</a>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-xs btn-warning">Edit</a>
        <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">No employees found.</td>
</tr>
@endforelse
