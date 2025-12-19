@extends('layouts.admin')

@section('title', 'Manage Announcements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Create Announcement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.announcements.store') }}" method="POST">
                        @csrf
                        @include('announcements._form')
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Announcement History</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Title</th>
                                <th>Type</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $item)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold d-block">{{ $item->title }}</span>
                                    <small class="text-muted">{{ Str::limit($item->message, 40) }}</small>
                                </td>
                                <td><span class="badge bg-{{ $item->type }}">{{ ucfirst($item->type) }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.announcements.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection