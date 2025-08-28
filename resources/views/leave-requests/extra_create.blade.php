@extends('layouts.admin')
@section('title', 'Request Extra Leave')
@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Extra Leave Application</h3>
    </div>
    <form method="POST" action="{{ route('leave-requests.extra-store') }}">
        @csrf
        <div class="card-body">
            <p>Dear Sir,</p>
            <p>Respectfully it is stated that I have availed all available leaves, but due to reasons specified below I need Extra Leave(s).</p>
            
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>I request you to kindly allot me extra leaves of</label>
                    <input type="number" name="days_requested" class="form-control" placeholder="No. of days" value="{{ old('days_requested') }}" required>
                    @error('days_requested') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label>From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    @error('start_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label>To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    @error('end_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Reason <span class="text-danger">*</span></label>
                <textarea name="reason" id="reason" class="form-control" rows="4" required>{{ old('reason') }}</textarea>
                @error('reason') <div class="text-danger mt-1">{{ $message }}</div> @enderror
            </div>
            <p>I shall be grateful if you kindly accept my request.</p>
            <hr>
            <p class="mt-4">Yours sincerely,</p>
            <p><strong>{{ Auth::user()->name }}</strong><br>
            <em>{{ $employee->designation }}</em></p>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection