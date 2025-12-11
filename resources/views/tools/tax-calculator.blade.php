@extends('layouts.admin')

@section('title', 'Tax Calculator')

@push('styles')
@if($isPopup)
<style>
    /* Hide AdminLTE wrapper elements when in popup mode */
    .main-header, .main-sidebar, .main-footer { display: none !important; }
    .content-wrapper { margin-left: 0 !important; min-height: 100vh !important; background-color: #f4f6f9; }
    .wrapper { width: 100%; }
</style>
@endif
<style>
    .calculator-container { max-width: 1100px; margin: 0 auto; }
    @media print {
        @page { size: A4; margin: 10mm; }
        body { background: #fff; margin: 0; }
        .no-print { display: none !important; }
        .main-header, .main-sidebar, .main-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
@endpush

@section('content')
<div class="calculator-container">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-calculator text-primary"></i> Tax Calculator
            </h3>
            <div class="no-print">
                @if($isPopup)
                    <button onclick="window.close()" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i> Close</button>
                @else
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                @endif
            </div>
        </div>

        <div class="card-body">
            {{-- Employee Info (If linked) --}}
            @if(isset($employee))
            <div class="border rounded p-3 mb-4 bg-light">
                <h5 class="mb-3 text-secondary"><i class="fas fa-user"></i> Employee Details</h5>
                <div class="row">
                    <div class="col-md-4"><strong>Name:</strong> {{ $employee->name }}</div>
                    <div class="col-md-4"><strong>CNIC:</strong> {{ $employee->cnic }}</div>
                    <div class="col-md-4"><strong>Designation:</strong> {{ $employee->designation ?? 'N/A' }}</div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Manual Entry Mode. Enter values below to simulate tax.
            </div>
            @endif

            <form method="POST" action="{{ route('tools.taxCalculator.calculate') }}">
                @csrf
                <input type="hidden" name="is_popup" value="{{ $isPopup }}">
                @if(isset($employee))
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                @endif

                <div class="row">
                    {{-- LEFT COLUMN: Inputs --}}
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0">Salary Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Employee Name</label>
                                    <input type="text" name="employee_name" class="form-control" value="{{ old('employee_name', $employeeName) }}" placeholder="Enter Name">
                                </div>

                                <div class="form-group p-2 border rounded bg-light">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" class="custom-control-input" id="joined_before_tax_year" name="joined_before_tax_year" value="1"
                                            {{ old('joined_before_tax_year', $isBeforeTaxYear ?? true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="joined_before_tax_year">Joined before start of Tax Year?</label>
                                    </div>
                                    
                                    <div id="joining_date_wrapper" style="{{ old('joined_before_tax_year', $isBeforeTaxYear ?? true) ? 'opacity:0.5; pointer-events:none;' : '' }}">
                                        <label class="small text-muted">Joining Date</label>
                                        <input type="date" name="joining_date" id="joining_date_input" class="form-control form-control-sm" 
                                               value="{{ old('joining_date', $joiningDate->format('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Tax Year</label>
                                    <select name="tax_year" class="form-control">
                                        @php
                                            $currentYear = now()->year;
                                            $selectedYear = old('tax_year', $taxData['tax_year'] ?? ($currentYear . '-' . ($currentYear + 1)));
                                        @endphp
                                        @foreach(range($currentYear-1, $currentYear+1) as $y)
                                            <option value="{{ $y }}" {{ (str_starts_with($selectedYear, $y)) ? 'selected' : '' }}>{{ $y . '-' . ($y+1) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <hr>

                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Basic Salary</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="monthly_salary" id="monthly_salary" class="form-control format-currency text-right"
                                            value="{{ old('monthly_salary', number_format($basic, 2)) }}" required placeholder="0.00">
                                    </div>
                                </div>

                                @foreach($allAllowances as $allowance)
                                    @php
                                        $val = old('components.'.$allowance->id, $employeeAllowances[$allowance->id] ?? 0);
                                    @endphp
                                    <div class="form-group row">
                                        <label class="col-sm-5 col-form-label small text-truncate" title="{{ $allowance->name }}">
                                            {{ $allowance->name }}
                                            @if($allowance->is_tax_exempt) <i class="fas fa-shield-alt text-success"></i> @endif
                                        </label>
                                        <div class="col-sm-7">
                                            <input type="text" name="components[{{ $allowance->id }}]" 
                                                   class="form-control format-currency allowance-input text-right form-control-sm"
                                                   data-is-exempt="{{ $allowance->is_tax_exempt }}"
                                                   data-exempt-type="{{ $allowance->exemption_type }}"
                                                   data-exempt-value="{{ $allowance->exemption_value }}"
                                                   value="{{ number_format($val, 2) }}" placeholder="0.00">
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong>Gross Salary:</strong>
                                        <span id="gross_display">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-primary">
                                        <strong>Taxable Salary:</strong>
                                        <span id="taxable_display">0.00</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success btn-block shadow no-print">
                                        <i class="fas fa-calculator mr-1"></i> Calculate Tax
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Results --}}
                    <div class="col-lg-7">
                        @if(isset($taxData))
                            <div class="card card-outline card-success h-100">
                                <div class="card-header">
                                    <h3 class="card-title text-success font-weight-bold">Calculation Result</h3>
                                    <div class="card-tools no-print">
                                        <button type="button" class="btn btn-tool" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-4 result-box">
                                        <div class="col-3 border-right">
                                            <h6>Gross Salary</h6>
                                            <h4 class="text-dark">{{ number_format($taxData['annual_income'], 0) }}</h4>
                                            <small class="text-muted d-block" style="font-size: 0.7rem; margin-top: 4px;">(Total)</small>
                                        </div>
                                        <div class="col-3 border-right">
                                            <h6>Taxable Income</h6>
                                            <h4 class="text-primary">{{ number_format($taxData['taxable_income'], 0) }}</h4>
                                        </div>
                                        <div class="col-3 border-right">
                                            <h6>Total Tax</h6>
                                            <h4 class="text-danger">{{ number_format($taxData['tax_payable'], 0) }}</h4>
                                        </div>
                                        <div class="col-3">
                                            <h6>Monthly Tax</h6>
                                            <h4 class="text-danger">{{ number_format($taxData['monthly_tax'], 0) }}</h4>
                                            <small class="text-muted d-block" style="font-size: 0.7rem; margin-top: 4px;">(Avg)</small>
                                        </div>
                                    </div>

                                    <h6 class="font-weight-bold border-bottom pb-2 mb-2">Month-wise Breakdown ({{ $taxData['tax_year'] }})</h6>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-striped table-hover text-center">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Month</th>
                                                    <th class="text-right">Tax Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($taxData['monthly_breakdown'] as $row)
                                                    <tr>
                                                        <td>{{ $row['month'] }}</td>
                                                        <td class="text-right text-danger">{{ number_format($row['tax'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="font-weight-bold">
                                                    <td>Total</td>
                                                    <td class="text-right">{{ number_format($taxData['tax_payable'], 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light text-center h-100 d-flex align-items-center justify-content-center border">
                                <div class="text-muted">
                                    <i class="fas fa-calculator fa-3x mb-3"></i><br>
                                    Enter salary details and click <strong>Calculate Tax</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatNumber(num) { return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(num || 0)); }
    function unformatNumber(str) { return parseFloat(String(str).replace(/,/g, '')) || 0; }

    const inputs = document.querySelectorAll('.format-currency');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            this.value = formatNumber(unformatNumber(this.value));
            updateTotals();
        });
    });

    function updateTotals() {
        let basic = unformatNumber(document.getElementById('monthly_salary').value);
        let gross = basic;
        let taxable = basic; 

        document.querySelectorAll('.allowance-input').forEach(el => {
            let amount = unformatNumber(el.value);
            gross += amount; 
            
            let isExempt = el.dataset.isExempt == '1';
            if (isExempt && el.dataset.exemptType === 'percentage_of_basic') {
                let limit = basic * ((parseFloat(el.dataset.exemptValue) || 0) / 100);
                let taxablePortion = Math.max(0, amount - limit);
                taxable += taxablePortion;
            } else {
                taxable += amount;
            }
        });
        
        document.getElementById('gross_display').innerText = 'Rs. ' + formatNumber(gross);
        document.getElementById('taxable_display').innerText = 'Rs. ' + formatNumber(taxable);
    }
    
    const checkbox = document.getElementById('joined_before_tax_year');
    const dateDiv = document.getElementById('joining_date_wrapper');
    checkbox.addEventListener('change', function() {
        if(this.checked) {
            dateDiv.style.opacity = '0.5';
            dateDiv.style.pointerEvents = 'none';
        } else {
            dateDiv.style.opacity = '1';
            dateDiv.style.pointerEvents = 'auto';
        }
    });

    updateTotals();
});
</script>
@endpush