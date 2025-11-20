@extends('layouts.admin')

@section('title', 'Salary Components')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Salary Components</h3>
        <a href="{{ route('salary-components.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle mr-1"></i> Add New Component
        </a>
    </div>

    <div class="card-body p-0">
        @if($components->count() > 0)
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Tax Exempt</th>
                        <th>Exemption Details</th>
                        <th style="width: 15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $index => $component)
                        <tr @if($component->is_tax_component) style="background-color: #fffbea;" @endif>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $component->name }}
                                @if($component->is_tax_component)
                                    <span class="badge badge-warning ml-1">Tax Deduction</span>
                                @endif
                            </td>
                            <td>
                                @if($component->type === 'allowance')
                                    <span class="badge badge-success">Allowance</span>
                                @else
                                    <span class="badge badge-danger">Deduction</span>
                                @endif
                            </td>
                            <td>
                                @if($component->is_tax_exempt)
                                    <span class="badge badge-info">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($component->is_tax_exempt)
                                    {{ $component->exemption_type === 'percentage_of_basic' ? 'Up to ' . $component->exemption_value . '% of Basic' : 'Custom Rule' }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('salary-components.edit', $component) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('salary-components.destroy', $component) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this component?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-4 text-center text-muted">
                No salary components found. <a href="{{ route('salary-components.create') }}">Add one now.</a>
            </div>
        @endif
    </div>
</div>
@endsection
