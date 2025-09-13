@extends('layouts.admin')

@section('title', 'Edit Credential')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Client Credential</h3>
    </div>
    <form method="POST" action="{{ route('client-credentials.update', $credential) }}">
        @csrf
        @method('PATCH')
        
        {{-- Include the shared form partial --}}
        @include('client-credentials._form', ['credential' => $credential])

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Credential</button>
            <a href="{{ route('client-credentials.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
