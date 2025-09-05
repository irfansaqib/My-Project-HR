@extends('layouts.admin')
@section('title', 'Manage Bank Accounts')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Business Bank Accounts</h3>
        <a href="{{ route('business-bank-accounts.create') }}" class="btn btn-primary float-right">Add New Account</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Account Title</th>
                    <th>Account Number</th>
                    <th>Default</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bankAccounts as $account)
                    <tr>
                        <td>{{ $account->bank_name }}</td>
                        <td>{{ $account->account_title }}</td>
                        <td>{{ $account->account_number }}</td>
                        <td>
                            @if($account->is_default)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('business-bank-accounts.edit', $account) }}" class="btn btn-xs btn-warning">Edit</a>
                            <form action="{{ route('business-bank-accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No bank accounts added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection