@extends('layouts.admin')
@section('title', 'Leave Encashments')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Encashment Requests</h3>
        <div class="card-tools">
            <a href="{{ route('leave-encashments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Days</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($encashments as $enc)
                <tr>
                    <td>{{ $enc->encashment_date->format('d M, Y') }}</td>
                    <td>{{ $enc->employee->name }}</td>
                    <td>{{ $enc->leaveType->name }}</td>
                    <td>{{ $enc->days }}</td>
                    <td>{{ number_format($enc->amount, 2) }}</td>
                    <td>
                        @if($enc->status == 'pending') <span class="badge badge-warning">Pending</span>
                        @elseif($enc->status == 'approved') <span class="badge badge-info">Approved</span>
                        @elseif($enc->status == 'paid') <span class="badge badge-success">Paid</span>
                        @else <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                    <td class="text-right">
                        {{-- 1. ADMIN ACTIONS (Approve/Reject) --}}
                        @hasanyrole('Owner|Admin')
                            @if($enc->status == 'pending')
                                <form action="{{ route('leave-encashments.approve', $enc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('leave-encashments.reject', $enc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        @endhasanyrole

                        {{-- 2. USER ACTIONS (Edit/Delete) - Only if Pending --}}
                        @if($enc->status == 'pending')
                            <a href="{{ route('leave-encashments.edit', $enc->id) }}" class="btn btn-xs btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('leave-encashments.destroy', $enc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-secondary"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-2">{{ $encashments->links() }}</div>
    </div>
</div>
@endsection