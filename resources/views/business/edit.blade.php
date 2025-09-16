@extends('layouts.admin')

@section('title', 'Edit Business Details')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Edit Business</h3>
    </div>
    <form method="POST" action="{{ route('business.update', $business) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('business._form')
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Business</button>
            {{-- ✅ DEFINITIVE FIX: Changed the broken route to the correct one. --}}
            <a href="{{ route('business.show', $business) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

