@extends('layouts.admin')
@section('title', 'Edit Tax Rates')

@section('content')
<form action="{{ route('tax-rates.update', $taxRate) }}" method="POST">
    @csrf
    @method('PUT')
    @include('tax-rates._form')
</form>
@endsection