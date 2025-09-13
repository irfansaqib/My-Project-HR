@extends('layouts.admin')

@section('title', 'Add New Credential')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Client Credential</h3>
    </div>
    <form method="POST" action="{{ route('client-credentials.store') }}">
        @csrf
        
        {{-- Include the shared form partial --}}
        @include('client-credentials._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Credential</button>
            <a href="{{ route('client-credentials.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
