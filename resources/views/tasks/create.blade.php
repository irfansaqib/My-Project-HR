@extends('layouts.admin')
@section('title', 'Create New Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Create New Task</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    @include('tasks._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection