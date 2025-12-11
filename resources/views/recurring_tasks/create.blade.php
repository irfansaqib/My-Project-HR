@extends('layouts.admin')
@section('title', 'Setup Recurring Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">Create Recurring Profile</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('recurring-tasks.store') }}" method="POST">
                    @csrf
                    @include('recurring_tasks._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection