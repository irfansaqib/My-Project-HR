@extends('layouts.admin')
@section('title', 'Add Salary Component')
@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Add New Salary Component</h3></div>
    <form action="{{ route('salary-components.store') }}" method="POST">
        @csrf
        
        @include('salary-components._form')

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Component</button>
            <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection