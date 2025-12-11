@extends('layouts.admin')
@section('title', 'Tax Certificate')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Generate Tax Certificate</h3></div>
            {{-- ✅ Points to View Route --}}
            <form action="{{ route('salaries.tax.view') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Financial Year</label>
                        <select name="fy" class="form-control">
                            @foreach($fys as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-eye mr-1"></i> Generate Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection