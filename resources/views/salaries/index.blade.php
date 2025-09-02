@extends('layouts.admin')
@section('title', 'Salary Sheets')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Generated Salary Sheets</h3>
        <a href="{{ route('salaries.create') }}" class="btn btn-primary float-right">Generate New Sheet</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Year</th>
                    <th>Payslips Generated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($processedMonths as $month)
                    <tr>
                        <td>{{ \Carbon\Carbon::create()->month($month->month)->format('F') }}</td>
                        <td>{{ $month->year }}</td>
                        <td>{{ $month->payslip_count }}</td>
                        <td>
                            <a href="{{ route('salaries.show', ['year' => $month->year, 'month' => $month->month]) }}" class="btn btn-sm btn-info">View Sheet</a>
                            <form action="{{ route('salaries.destroy', ['year' => $month->year, 'month' => $month->month]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entire salary sheet?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No salary sheets have been generated yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection