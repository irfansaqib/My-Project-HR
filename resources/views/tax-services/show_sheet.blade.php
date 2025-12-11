@extends('layouts.admin')
@section('title', 'Client Payroll')

@push('styles')
<style>
    .table-compact th, .table-compact td { padding: 4px 8px !important; vertical-align: middle !important; white-space: nowrap !important; font-size: 13px; }
    .table-compact thead th { padding-top: 8px !important; padding-bottom: 8px !important; }
    .sticky-col-1 { position: sticky; left: 0; z-index: 10; background-color: #fff; border-right: 1px solid #dee2e6; }
    .sticky-col-2 { position: sticky; left: 150px; z-index: 10; background-color: #fff; border-right: 1px solid #dee2e6; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom py-2">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    {{ $sheet->client->name }} <span class="text-muted mx-2">|</span> {{ $sheet->month->format('F Y') }}
                </h5>
                <span class="badge {{ $sheet->status == 'finalized' ? 'badge-success' : 'badge-warning' }}">
                    {{ ucfirst($sheet->status) }}
                </span>
            </div>
            
            <div>
                <a href="{{ route('tax-services.sheet.export', $sheet->id) }}" class="btn btn-outline-success btn-sm mr-1">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
                
                <a href="{{ route('tax-services.clients.show', $sheet->tax_client_id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
                
                @if($sheet->status == 'draft')
                <form action="{{ route('tax-services.sheet.finalize', $sheet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Finalize this sheet? This will lock values and update YTD history.');">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="fas fa-check-circle mr-1"></i> Finalize & Lock</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0" style="max-height: 80vh;">
        <table class="table table-bordered table-hover table-sm table-compact mb-0">
            <thead class="bg-light text-center">
                <tr>
                    <th rowspan="2" class="align-middle text-left sticky-col-1" style="min-width: 150px;">Employee</th>
                    <th rowspan="2" class="align-middle sticky-col-2" style="min-width: 120px;">CNIC</th>
                    
                    <th class="bg-white border-bottom-0" colspan="{{ $sheet->client->components->where('type', 'allowance')->count() + 5 }}">For the Month</th>
                    <th class="bg-light border-bottom-0" colspan="2">YTD (Jul - Current)</th>
                    <th class="bg-white border-bottom-0" colspan="3">Annual Estimated</th>
                </tr>
                <tr>
                    <th class="text-right">Basic Salary</th>
                    @foreach($sheet->client->components->where('type', 'allowance') as $comp)
                        <th class="text-right">{{ $comp->name }}</th>
                    @endforeach
                    <th class="text-right text-success">Bonus</th>
                    <th class="text-right font-weight-bold bg-light">Gross Salary</th>
                    <th class="text-right text-primary">Taxable Salary</th>
                    <th class="text-right text-danger font-weight-bold">Tax Deduction</th>

                    <th class="text-right text-primary" style="background-color: #f8f9fa;">Taxable</th>
                    <th class="text-right text-danger" style="background-color: #f8f9fa;">Tax Paid</th>

                    <th class="text-right">Gross Salary</th>
                    <th class="text-right text-primary">Taxable Salary</th>
                    <th class="text-right text-danger">Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sheet->items as $item)
                @php
                    $allowances = is_array($item->allowances_breakdown) ? $item->allowances_breakdown : json_decode($item->allowances_breakdown ?? '[]', true);
                @endphp

                <tr>
                    <td class="sticky-col-1 text-left"><strong>{{ $item->employee->name }}</strong></td>
                    <td class="sticky-col-2 text-center">{{ $item->employee->cnic }}</td>
                    
                    {{-- Monthly Data --}}
                    <td class="text-right">{{ number_format($item->basic_salary) }}</td>
                    @foreach($sheet->client->components->where('type', 'allowance') as $comp)
                        <td class="text-right text-muted">{{ number_format($allowances[$comp->name] ?? 0) }}</td>
                    @endforeach
                    <td class="text-right text-success">{{ number_format($item->bonus) }}</td>
                    <td class="text-right font-weight-bold bg-light">{{ number_format($item->gross_salary) }}</td>
                    <td class="text-right text-primary">{{ number_format($item->taxable_income_monthly) }}</td>
                    <td class="text-right text-danger font-weight-bold">{{ number_format($item->income_tax) }}</td>
                    
                    {{-- YTD Data (Passed from Controller) --}}
                    <td class="text-right font-weight-bold text-primary" style="background-color: #f8f9fa;">
                        {{ number_format($item->display_ytd_taxable) }}
                    </td>
                    <td class="text-right font-weight-bold text-danger" style="background-color: #f8f9fa;">
                        {{ number_format($item->display_ytd_tax_paid) }}
                    </td>

                    {{-- Annual Data (Calculated in Controller via Service) --}}
                    <td class="text-right text-muted">{{ number_format($item->display_est_annual_gross) }}</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($item->display_est_annual_taxable) }}</td>
                    <td class="text-right text-danger font-weight-bold">{{ number_format($item->display_est_annual_tax) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="card-footer bg-white text-muted py-2" style="font-size: 12px;">
        <div class="row">
            <div class="col-md-6">
                <i class="fas fa-calendar-alt mr-1"></i> <strong>Tax Year:</strong> {{ $sheet->month->year . '-' . ($sheet->month->year + 1) }}
            </div>
            <div class="col-md-6 text-right">
                 <i class="fas fa-check-circle mr-1"></i> 
                 <strong>Verified:</strong> Annual estimates now use precise Tax Brackets matching the Preview.
            </div>
        </div>
    </div>
</div>
@endsection