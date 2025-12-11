@extends('layouts.tax_client')

@section('tab-content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-left-info">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-print mr-2"></i> Generate Tax Certificates
                </h5>
            </div>
            
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Generate annual withholding tax certificates for employees (Section 149). You can print for a specific employee or all employees at once.
                </p>

                <form action="{{ route('tax-services.clients.certificates.print', $client->id) }}" method="POST" target="_blank">
                    @csrf
                    
                    {{-- TAX YEAR SELECT --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Select Tax Year</label>
                        <select name="tax_year" class="form-control">
                            @for($y = 2024; $y <= date('Y'); $y++)
                                <option value="{{ $y }}">{{ $y }} - {{ $y + 1 }}</option>
                            @endfor
                        </select>
                        <small class="form-text text-muted">Fiscal Year (July 1st to June 30th)</small>
                    </div>

                    {{-- EMPLOYEE SELECT --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Select Employee</label>
                        <select name="employee_id" class="form-control select2">
                            <option value="all">-- Generate for All Employees --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->cnic }})</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-info btn-block font-weight-bold shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i> Generate Certificate(s)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection