@extends('layouts.admin')
@section('title', 'Add New Tax Rates')

@section('content')
<form action="{{ route('tax-rates.store') }}" method="POST">
    @csrf
    @include('tax-rates._form')
</form>
@endsection