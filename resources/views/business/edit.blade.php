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

        <div class="card-footer d-flex justify-content-between">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Update Business
                </button>
                <a href="{{ route('business.show', $business) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
