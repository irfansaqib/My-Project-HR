@extends('layouts.admin')
@section('title', 'Review Salary Revision')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Salary Revision Approval for: {{ $employee->name }}
        </h3>
    </div>

    <div class="card-body">
        <form>
            <div class="form-group">
                <label>Effective Date</label>
                <input type="text" value="{{ \Carbon\Carbon::parse($structure->effective_date)->format('d M, Y') }}" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label>Basic Salary</label>
                <input type="text" class="form-control" value="{{ number_format($structure->basic_salary, 2) }}" readonly>
            </div>

            <h5 class="mt-4"><strong>Allowances & Deductions</strong></h5>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($structureData as $component)
                        <tr>
                            <td>{{ $component['name'] }}</td>
                            <td class="text-right {{ $component['type'] == 'deduction' ? 'text-danger' : '' }}">
                                {{ $component['type'] == 'deduction' ? '(' . number_format($component['amount'], 2) . ')' : number_format($component['amount'], 2) }}
                            </td>
                            <td class="text-center">{{ ucfirst($component['type']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <div>
                    <p><strong>Gross Salary:</strong> {{ number_format($employee->gross_salary, 2) }}</p>
                    <p><strong>Net Payable:</strong> {{ number_format($employee->net_salary, 2) }}</p>
                </div>
                <div class="text-right">
                    @if($structure->status === 'pending')
                        <form action="{{ route('salary.revisions.approve', $structure->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <form action="{{ route('salary.revisions.reject', $structure->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    @else
                        <span class="badge badge-info">{{ ucfirst($structure->status) }}</span>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
