@extends('layouts.admin')
@section('title', 'Salary Components')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Salary Components</h3>
            <a href="{{ route('salary-components.create') }}" class="btn btn-primary float-right">Add New Component</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Allowances</h5>
                    <table class="table table-sm table-bordered">
                        @forelse($allowances as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td width="120">
                                <a href="{{ route('salary-components.edit', $item) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form action="{{ route('salary-components.destroy', $item) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td class="text-center">No allowances defined.</td></tr>
                        @endforelse
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Deductions</h5>
                    <table class="table table-sm table-bordered">
                        @forelse($deductions as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td width="120">
                                <a href="{{ route('salary-components.edit', $item) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form action="{{ route('salary-components.destroy', $item) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td class="text-center">No deductions defined.</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection