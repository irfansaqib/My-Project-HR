@extends('layouts.admin')
@section('title', 'Add New Client')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-white py-3 border-left-primary">
                <h6 class="m-0 font-weight-bold text-primary">Client Registration Form</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('clients.store') }}" method="POST">
                    @csrf
                    {{-- Include the Partial --}}
                    @include('clients._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection