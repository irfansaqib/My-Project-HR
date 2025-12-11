@extends('layouts.admin')
@section('title', 'Edit Recurring Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Edit Recurring Profile #{{ $recurringTask->id }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('recurring-tasks.update', $recurringTask->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- Pass the $profile variable along with dropdown data --}}
                    @include('recurring_tasks._form', [
                        'profile' => $recurringTask,
                        'selectedLvl0' => $recurringTask->category->parent->parent_id ?? null,
                        'selectedLvl1' => $recurringTask->category->parent_id ?? null,
                        'selectedLvl2' => $recurringTask->task_category_id
                    ])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection