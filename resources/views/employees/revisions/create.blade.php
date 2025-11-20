@extends('layouts.admin')
@section('title', 'Create Salary Revision')

@section('content')
@include('employees.revisions._form', [
    'formAction' => route('employees.revisions.store', $employee),
    'isEdit' => false,
])
@endsection
