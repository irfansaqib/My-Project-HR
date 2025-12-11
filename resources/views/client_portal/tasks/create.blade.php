@extends('layouts.client_portal')
@section('header', 'Submit New Request')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('client.tasks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('client_portal.tasks._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection