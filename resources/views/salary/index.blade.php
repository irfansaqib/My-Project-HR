@extends('layouts.admin')
@section('title', 'Salary Sheets')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Generated Salary Sheets</h3>
        <a href="{{ route('salaries.create') }}" class="btn btn-primary float-right">Generate New Sheet</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Payslips Generated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- THIS SECTION IS NOW CORRECTED --}}
                    @forelse ($salarySheets as $sheet)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($sheet->month)->format('F, Y') }}</td>
                            <td>{{ $sheet->items_count }}</td>
                            <td>
                                <a href="{{ route('salaries.show', $sheet->id) }}" class="btn btn-sm btn-info">View Sheet</a>
                                <form action="{{ route('salaries.destroy', $sheet->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entire salary sheet?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No salary sheets have been generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Add pagination links --}}
        @if($salarySheets->hasPages())
            <div class="card-footer clearfix">
                {{ $salarySheets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection