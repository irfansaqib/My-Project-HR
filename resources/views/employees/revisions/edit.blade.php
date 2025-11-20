@extends('layouts.admin')
@section('title', 'Edit Salary Revision')

@section('content')
@include('employees.revisions._form', [
    'formAction' => route('employees.revisions.update', [$employee->id, $revision->id]),
    'isEdit' => true,
])
@endsection
