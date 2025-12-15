@extends('layouts.admin')
@section('title', 'Edit Client')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-white py-3 border-left-warning">
                <h6 class="m-0 font-weight-bold text-warning">Edit Client Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- 
                        Pass the $client object to the partial.
                        $employees is passed from the Controller to the View automatically.
                    --}}
                    @include('clients._form', ['client' => $client])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection