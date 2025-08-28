@extends('layouts.admin')

@section('title', 'Edit Leave Application')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Leave Application</h3>
    </div>
    <form method="POST" action="{{ route('leave-requests.update', $leaveRequest) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        @include('leave-requests._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Application</button>
            <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection