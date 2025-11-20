<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Calculator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Load Styles --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    @if(!$isPopup)
        <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @endif

    <style>
        body { background: #f4f6f9; font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .calculator-container { max-width: 1100px; margin: 20px auto; }
        .card { border: none; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); border-radius: 0.25rem; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,.125); }
        
        /* Consistent Input Styling */
        .form-control.text-right { text-align: right; }
        
        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: #fff; margin: 0; }
            .no-print { display: none !important; }
            .calculator-container { max-width: 100%; margin: 0; width: 100%; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .btn, .alert { display: none; }
        }
    </style>
</head>
<body class="{{ $isPopup ? '' : 'sidebar-mini layout-fixed' }}">

<div class="{{ $isPopup ? 'container-fluid p-3' : 'wrapper' }}">

    @if(!$isPopup)
        @include('layouts.admin_header_partial')
    @endif

    <div class="calculator-container">
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-dark"><i class="fas fa-calculator text-primary mr-2"></i> Salary Tax Calculator</h3>
            <div class="no-print">
                @if($isPopup)
                    <button onclick="window.close()" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i> Close</button>
                @else
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                @endif
            </div>
        </div>

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

                            {{-- Basic Salary (Cleaned up) --}}
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Basic Salary</label>
                                <div class="col-sm-7">
                                    <input type="text" name="monthly_salary" id="monthly_salary" class="form-control format-currency text-right"
                                        value="{{ old('monthly_salary', number_format($basic, 2)) }}" required placeholder="0.00">
                                </div>
                            </div>

                            {{-- Allowances (Consistent style) --}}
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

                            {{-- Live Totals --}}
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
                                {{-- Summary Grid --}}
                                <div class="row text-center mb-4 result-box">
                                    {{-- 1. Gross Salary --}}
                                    <div class="col-3 border-right">
                                        <h6>Gross Salary</h6>
                                        <h4 class="text-dark">{{ number_format($taxData['annual_income'], 0) }}</h4>
                                        <small class="text-muted d-block" style="font-size: 0.7rem; margin-top: 4px;">(Total)</small>
                                    </div>
                                    {{-- 2. Taxable --}}
                                    <div class="col-3 border-right">
                                        <h6>Taxable Income</h6>
                                        <h4 class="text-primary">{{ number_format($taxData['taxable_income'], 0) }}</h4>
                                    </div>
                                    {{-- 3. Total Tax --}}
                                    <div class="col-3 border-right">
                                        <h6>Total Tax</h6>
                                        <h4 class="text-danger">{{ number_format($taxData['tax_payable'], 0) }}</h4>
                                    </div>
                                    {{-- 4. Monthly Tax --}}
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
</body>
</html>