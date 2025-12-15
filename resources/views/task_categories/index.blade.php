@extends('layouts.admin')
@section('title', 'Task Categories')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">Task Categories Hierarchy</h5>
        <a href="{{ route('task-categories.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus mr-1"></i> Add Category
        </a>
    </div>
    <div class="card-body p-0">
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 30%;">Root Category</th>
                        <th style="width: 30%;">Sub-Category (Level 1)</th>
                        <th style="width: 30%;">Task / Child (Level 2)</th>
                        <th class="text-center" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $root)
                        {{-- ROOT ROW --}}
                        <tr class="bg-light font-weight-bold">
                            <td>{{ $root->name }}</td>
                            <td colspan="2" class="text-muted small pl-3">Root</td>
                            <td class="text-center">
                                <a href="{{ route('task-categories.edit', $root->id) }}" class="btn btn-xs btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('task-categories.destroy', $root->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        {{-- LEVEL 1 LOOP --}}
                        @foreach($root->children as $child)
                            <tr>
                                <td class="border-0"></td>
                                <td class="text-primary">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i> {{ $child->name }}
                                </td>
                                <td class="text-muted small">-</td>
                                <td class="text-center">
                                    <a href="{{ route('task-categories.edit', $child->id) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('task-categories.destroy', $child->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            {{-- LEVEL 2 LOOP --}}
                            @foreach($child->children as $sub)
                                <tr>
                                    <td class="border-0"></td>
                                    <td class="border-0"></td>
                                    <td><i class="fas fa-angle-right mr-2 text-muted"></i> {{ $sub->name }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('task-categories.edit', $sub->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('task-categories.destroy', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-sitemap fa-3x mb-3 text-gray-300"></i><br>
                                No categories found. <a href="{{ route('task-categories.create') }}">Create the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection