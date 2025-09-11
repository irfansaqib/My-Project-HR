@extends('layouts.admin')
@section('title', 'Shift Details')

@section('content')
<div class="card">
    {{-- ** BACKGROUND COLOR AND BORDER ADDED ** --}}
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f8f9fa; border-top: 3px solid #17a2b8;">
        <h3 class="card-title" style="font-size: 1.25rem;">Details for: {{ $shift->name }}</h3>
        <div>
            <a href="{{ route('shifts.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-warning">Edit Shift</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title m-0"><i class="fas fa-clock mr-2"></i>Shift Timings</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Start Time</dt>
                            <dd class="col-sm-7">{{ $shift->start_time->format('h:i A') }}</dd>

                            <dt class="col-sm-5">End Time</dt>
                            <dd class="col-sm-7">{{ $shift->end_time->format('h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title m-0"><i class="fas fa-sign-in-alt mr-2"></i>Punch Acceptance Window</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Window Opens At</dt>
                            <dd class="col-sm-7">{{ $shift->punch_in_window_start->format('h:i A') }}</dd>

                            <dt class="col-sm-5">Window Closes At</dt>
                            <dd class="col-sm-7">{{ $shift->punch_in_window_end->format('h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h5 class="card-title m-0"><i class="fas fa-cog mr-2"></i>Other Details</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Grace Period (Late Arrival)</dt>
                            <dd class="col-sm-9">{{ $shift->grace_period_in_minutes }} minutes</dd>

                            <dt class="col-sm-3">Weekly Off Days</dt>
                            <dd class="col-sm-9">{{ $shift->weekly_off ?: 'None specified' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection