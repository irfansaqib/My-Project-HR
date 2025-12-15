@extends('layouts.client_portal')

@section('header', 'Messages')

@section('content')
<div class="card card-stat">
    <div class="card-body text-center py-5">
        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
        <h4>Message Center</h4>
        <p class="text-muted">You have no new messages.</p>
    </div>
</div>
@endsection