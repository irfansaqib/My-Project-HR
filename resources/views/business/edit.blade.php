@extends('layouts.admin')

@section('title', 'Edit Business Details')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Business: {{ $business->business_name }}</h3>
    </div>
    <form method="POST" action="{{ route('business.update', $business) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
        @include('business._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Details</button>
            <a href="{{ route('business.show', $business) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection