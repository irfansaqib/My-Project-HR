@extends('layouts.admin')
@section('title', 'Edit Leave Encashment')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Edit Encashment Request</h3>
    </div>
    <form action="{{ route('leave-encashments.update', $encashment->id) }}" method="POST">
        @method('PUT')
        <div class="card-body">
            @include('leave-encashments._form', ['encashment' => $encashment, 'buttonText' => 'Update Request'])
        </div>
    </form>
</div>
@endsection