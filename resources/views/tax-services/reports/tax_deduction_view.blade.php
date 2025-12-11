@extends('layouts.tax_client')

@section('tab-content')

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">Tax Deduction Report</h5>
            <button onclick="window.print()" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
    
    <div class="card-body">
        
        {{-- REPORT HEADER (Matching CSV Format) --}}
        <div class="text-center mb-4">
            <h2 class="font-weight-bold text-uppercase text-dark mb-1">{{ $client->name }}</h2>
            <h5 class="text-secondary font-weight-bold">Tax Deduction Detail</h5>
            <p class="text-muted mb-0">
                For the Period <strong>{{ $start->format('F, Y') }}</strong> to <strong>{{ $end->format('F, Y') }}</strong>
            </p>
        </div>

        {{-- REPORT TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark text-center">
                    <tr>
                        <th style="width: 50px;">S.No</th>
                        <th>Payment Section</th>
                        <th>Employee NTN</th>
                        <th>Employee CNIC</th>
                        <th>Employee Name</th>
                        <th>Employee City</th>
                        <th>Employee Address</th>
                        <th>Employee Status</th>
                        <th>Gross Salary</th>
                        <th>Tax Deducted</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalGross = 0; $totalTax = 0; @endphp
                    @forelse($items as $index => $item)
                        @php 
                            $totalGross += $item->gross_salary; 
                            $totalTax += $item->income_tax; 
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">149(1)</td>
                            <td class="text-center">{{-- NTN --}}</td> 
                            <td class="text-center">{{ $item->employee->cnic }}</td>
                            <td class="font-weight-bold">{{ $item->employee->name }}</td>
                            <td class="text-center">{{-- City --}}</td>
                            <td class="text-center">{{-- Address --}}</td>
                            <td class="text-center">Individual</td>
                            <td class="text-right">{{ number_format($item->gross_salary) }}</td>
                            <td class="text-right text-danger font-weight-bold">{{ number_format($item->income_tax) }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->sheet->month)->endOfMonth()->format('d-M-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                No finalized tax records found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($items->count() > 0)
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="8" class="text-right">TOTAL:</td>
                        <td class="text-right">{{ number_format($totalGross) }}</td>
                        <td class="text-right text-danger">{{ number_format($totalTax) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection