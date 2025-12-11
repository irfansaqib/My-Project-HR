<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Sheet - {{ $monthName }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; font-size: 8pt; }
        .print-container { margin: 20px; }
        .header-logo { max-height: 60px; }
        .sheet-title { font-size: 14pt; font-weight: normal; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; }
        th, td { padding: 4px 6px !important; text-align: center; vertical-align: middle !important; }
        th { font-weight: bold; background-color: #f2f2f2 !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        tfoot tr { font-weight: bold; background-color: #e9ecef !important; }
        .signature-area { margin-top: 60px; page-break-inside: avoid; }
        .signature-area p { margin-top: 0; }
        
        @media print {
            @page {
                size: landscape;
                margin: 20px;
            }
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="text-right mb-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">Print</button>
            <a href="{{ route('salaries.show', $salarySheet) }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
            <div>
                <h4>{{ $business->legal_name }}</h4>
                <p class="mb-0 text-muted sheet-title">Salary Sheet for the Month of {{ $monthName }}</p>
            </div>
            <div class="text-right">
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Business Logo" class="header-logo">
                @endif
            </div>
        </div>

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th rowspan="2">Sr.</th>
                    <th rowspan="2" style="width: 20%;">Employee</th>
                    <th rowspan="2">Designation</th>
                    <th rowspan="2">Basic Salary</th>
                    
                    {{-- Allowances Header --}}
                    @if($allowanceHeaders->count() > 0)
                        <th colspan="{{ $allowanceHeaders->count() }}">Allowances</th>
                    @endif
                    
                    <th rowspan="2">Bonus</th>
                    <th rowspan="2">Gross Salary</th>
                    
                    {{-- Deductions Header --}}
                    @if($deductionHeaders->count() > 0)
                        <th colspan="{{ $deductionHeaders->count() }}">Deductions</th>
                    @endif
                    
                    {{-- ✅ FIX: Removed hardcoded Income Tax Header --}}
                    
                    <th rowspan="2">Net Salary</th>
                    <th rowspan="2" style="width: 12%;">Bank Account</th>
                    <th rowspan="2" style="width: 8%;">Signature</th>
                </tr>
                <tr>
                    {{-- Dynamic Sub-headers --}}
                    @foreach($allowanceHeaders as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                    @foreach($deductionHeaders as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($salarySheet->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item->employee->employee_number }} | {{ $item->employee->name }}</td>
                    <td class="text-left">{{ $item->employee->designationRelation->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->employee->basic_salary, 0) }}</td>
                    
                    @foreach($allowanceHeaders as $header)
                        <td class="text-right">{{ number_format($item->allowances_breakdown[$header] ?? 0, 0) }}</td>
                    @endforeach
                    
                    <td class="text-right">{{ number_format($item->bonus, 0) }}</td>
                    <td class="text-right">{{ number_format($item->gross_salary, 0) }}</td>
                    
                    @foreach($deductionHeaders as $header)
                        <td class="text-right">{{ number_format($item->deductions_breakdown[$header] ?? 0, 0) }}</td>
                    @endforeach
                    
                    {{-- ✅ FIX: Removed hardcoded Income Tax Cell --}}

                    <td class="text-right font-weight-bold">{{ number_format($item->net_salary, 0) }}</td>
                    <td class="text-left">{{ $item->employee->bank_account_number ?? 'N/A' }}</td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td class="text-right">{{ number_format($salarySheet->items->sum('employee.basic_salary'), 0) }}</td>
                    
                    @foreach($allowanceHeaders as $header)
                        <td class="text-right">{{ number_format($salarySheet->items->sum(fn($item) => $item->allowances_breakdown[$header] ?? 0), 0) }}</td>
                    @endforeach
                    
                    <td class="text-right">{{ number_format($salarySheet->items->sum('bonus'), 0) }}</td>
                    <td class="text-right">{{ number_format($salarySheet->items->sum('gross_salary'), 0) }}</td>
                    
                    @foreach($deductionHeaders as $header)
                         <td class="text-right">{{ number_format($salarySheet->items->sum(fn($item) => $item->deductions_breakdown[$header] ?? 0), 0) }}</td>
                    @endforeach
                    
                    {{-- ✅ FIX: Removed hardcoded Income Tax Total --}}

                    <td class="text-right">{{ number_format($salarySheet->items->sum('net_salary'), 0) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-between signature-area">
            <div class="text-center">
                <p>_________________________</p>
                <p><strong>Verified by</strong></p>
            </div>
            <div class="text-center">
                <p>_________________________</p>
                <p><strong>Approved by</strong></p>
            </div>
        </div>
    </div>
</body>
</html>