@extends('layouts.admin')
@section('title', 'Update Salary Structure for ' . $employee->name)

@section('content')
<div class="container">
    <form action="{{ route('employees.salary.store', $employee) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4>Update Salary Structure for: <strong>{{ $employee->name }}</strong></h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="effective_date"><strong>Effective Date</strong></label>
                        <input type="date" name="effective_date" id="effective_date" class="form-control" value="{{ old('effective_date', now()->format('Y-m-d')) }}" required>
                        <small class="form-text text-muted">This is the date from which this new salary structure will be active.</small>
                    </div>
                </div>

                <hr>

                <h5>Salary Components</h5>

                {{-- Basic Salary --}}
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <label for="basic_salary">Basic Salary</label>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">PKR</span>
                            </div>
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control" value="{{ old('basic_salary', $currentStructure['basic_salary']) }}" required>
                        </div>
                    </div>
                </div>

                {{-- Allowances --}}
                <h6 class="mt-4 text-success">Allowances</h6>
                @php
                    $allowances = $employee->salaryComponents->where('type', 'allowance');
                    $allowancesCount = $allowances->count();
                @endphp
                @foreach($allowances as $index => $component)
                    <div class="row align-items-center mb-2">
                        <div class="col-md-4">
                            <label>{{ $component->name }}</label>
                            <input type="hidden" name="components[{{$index}}][name]" value="{{ $component->name }}">
                            <input type="hidden" name="components[{{$index}}][type]" value="allowance">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">PKR</span>
                                </div>
                                <input type="number" step="0.01" name="components[{{$index}}][amount]" class="form-control" value="{{ old('components.'.$index.'.amount', $currentStructure['components'][$component->name] ?? 0) }}" required>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Deductions --}}
                <h6 class="mt-4 text-danger">Deductions</h6>
                @foreach($employee->salaryComponents->where('type', 'deduction') as $index => $component)
                     <div class="row align-items-center mb-2">
                        <div class="col-md-4">
                            <label>{{ $component->name }}</label>
                            {{-- ✅ DEFINITIVE FIX: Use the allowance count as an offset instead of the faulty $loop->parent --}}
                            <input type="hidden" name="components[{{ $allowancesCount + $index }}][name]" value="{{ $component->name }}">
                            <input type="hidden" name="components[{{ $allowancesCount + $index }}][type]" value="deduction">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">PKR</span>
                                </div>
                                {{-- ✅ DEFINITIVE FIX: Apply the same offset logic to the amount input --}}
                                <input type="number" step="0.01" name="components[{{ $allowancesCount + $index }}][amount]" class="form-control" value="{{ old('components.'.($allowancesCount + $index).'.amount', $currentStructure['components'][$component->name] ?? 0) }}" required>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save New Salary Structure</button>
            </div>
        </div>
    </form>
</div>
@endsection