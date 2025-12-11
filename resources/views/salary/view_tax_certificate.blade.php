@extends('layouts.admin')
@section('title', 'Tax Certificate Preview')

@section('content')
<div class="container">
    <div class="row mb-3 no-print">
        <div class="col-12 text-right">
            <form action="{{ route('salaries.tax.download') }}" method="POST" target="_blank" class="d-inline">
                @csrf
                <input type="hidden" name="fy" value="{{ $fy }}">
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-file-pdf mr-1"></i> Download PDF</button>
            </form>
            <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <div class="card shadow-lg p-5" id="certificate-box">
        <div class="text-center mb-4">
            <h3 class="font-weight-bold text-uppercase mb-1">{{ $business->legal_name ?? $business->name }}</h3> {{-- ✅ Legal Name --}}
            <h5 class="text-muted">Annual Salary & Tax Deduction Certificate</h5>
            <p>Financial Year: {{ $fy }}</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Employee Name:</strong> {{ $employee->name }} <br>
                <strong>CNIC:</strong> {{ $employee->cnic }} <br>
                <strong>Designation:</strong> {{ $designation }} <br>
                <strong>Department:</strong> {{ $department }}
            </div>
            <div class="col-md-6 text-right">
                <strong>Date of Issue:</strong> {{ $date }} <br>
                <strong>Period:</strong> {{ $periodText }}
            </div>
        </div>

        <p class="lead text-justify">
            This is to certify that the sum of <strong>PKR {{ number_format($totalTax) }}</strong> has been deducted as Income Tax 
            from the salary of the above-mentioned employee for the period <strong>{{ $periodText }}</strong>.
        </p>

        <table class="table table-bordered mt-4">
            <thead class="thead-light">
                <tr><th>Month</th><th class="text-right">Gross Salary (PKR)</th><th class="text-right">Tax Deducted (PKR)</th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->salarySheet->month->format('F, Y') }}</td>
                    <td class="text-right">{{ number_format($item->gross_salary) }}</td>
                    <td class="text-right">{{ number_format($item->income_tax) }}</td>
                </tr>
                @endforeach
                <tr class="bg-light font-weight-bold">
                    <td>Total</td>
                    <td class="text-right">{{ number_format($totalIncome) }}</td>
                    <td class="text-right">{{ number_format($totalTax) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-5 pt-5">
            <div class="border-top d-inline-block pt-2" style="width: 200px; text-align: center;">
                Authorized Signatory
            </div>
        </div>
    </div>
</div>
@endsection