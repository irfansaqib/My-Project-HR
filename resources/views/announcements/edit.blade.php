@extends('layouts.admin')

@section('title', 'Edit Announcement')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Announcement</h5>
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-light text-primary">Cancel</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.announcements.update', $announcement->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('announcements._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection