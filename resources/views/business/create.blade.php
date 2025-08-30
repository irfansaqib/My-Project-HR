@extends('layouts.admin')

@section('title', 'Create Business Profile')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Create Your Business Profile</h3>
    </div>
    <form method="POST" action="{{ route('business.store') }}" enctype="multipart/form-data">
        @csrf
        
        @include('business._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Business</button>
        </div>
    </form>
</div>
@endsection