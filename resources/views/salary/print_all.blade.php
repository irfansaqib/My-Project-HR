<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Payslips - {{ $business->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .page-container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; page-break-after: always; position: relative; height: 95vh; }
        .print-date { position: absolute; top: 0; right: 0; font-size: 9px; color: #777; text-align: right; }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 15px; }
        .logo { max-height: 50px; margin-bottom: 5px; }
        .company-name { font-size: 20px; font-weight: bold; margin: 0; color: #222; }
        .company-address { font-size: 10px; color: #666; margin-top: 5px; }
        .payslip-title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; background: #f4f6f9; padding: 6px; border-radius: 4px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; border-bottom: 1px solid #eee; width: 50%; }
        .label { font-weight: bold; color: #555; display: inline-block; width: 110px; }
        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 0; border: 1px solid #ddd; }
        .salary-table th { background: #f8f9fa; padding: 6px; text-align: left; font-weight: bold; border-bottom: 1px solid #ddd; width: 25%; font-size: 10px; }
        .salary-table td { padding: 5px 6px; border-bottom: 1px solid #f9f9f9; vertical-align: top; }
        .amount-col { text-align: right; border-right: 1px solid #ddd; }
        .last-col { border-right: none; }
        .text-danger { color: #dc3545; }
        .totals-table { width: 100%; border-collapse: collapse; border: 1px solid #ddd; border-top: none; margin-bottom: 15px; }
        .totals-table td { width: 25%; padding: 8px; font-weight: bold; background: #e9ecef; }
        .totals-table .amount { text-align: right; }
        .payment-summary { margin-bottom: 15px; border: 1px solid #007bff; border-radius: 4px; overflow: hidden; }
        .payment-header { background: #007bff; color: #fff; padding: 6px; font-weight: bold; text-align: center; }
        .payment-body { padding: 10px; display: flex; justify-content: space-between; }
        .pay-item { text-align: center; width: 33%; }
        .pay-label { display: block; font-size: 9px; color: #666; text-transform: uppercase; }
        .pay-value { display: block; font-size: 13px; font-weight: bold; margin-top: 3px; }
        .amount-words { font-style: italic; color: #555; font-size: 10px; padding: 8px; border-top: 1px solid #eee; background: #fcfcfc; }
        .funds-box { border: 1px solid #28a745; border-radius: 4px; padding: 10px; margin-top: 15px; background: #f9fff9; }
        .funds-title { color: #28a745; font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #c3e6cb; padding-bottom: 3px; font-size: 11px; }
        .funds-grid { display: table; width: 100%; }
        .fund-item { display: table-cell; padding-right: 15px; font-size: 10px; }
        .fund-amount { color: #28a745; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
        @media print { body { -webkit-print-color-adjust: exact; } .no-print { display: none; } .page-container { border: none; height: auto; margin-bottom: 0; page-break-after: always; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; padding: 15px; background: #f4f4f4; border-bottom: 1px solid #ddd;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #333; color: #fff; border: none; cursor: pointer; border-radius: 4px;">Print All Payslips</button>
    </div>

    @foreach($payslips as $payslip)
    <div class="page-container">
        
        <div class="print-date">Printed On: {{ now()->format('d M, Y') }}</div>

        <div class="header">
            @if($business->logo_path)
                <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Logo" class="logo">
            @endif
            <h1 class="company-name">{{ $business->name }}</h1>
            <div class="company-address">{{ $business->address }}</div>
        </div>

        <div class="payslip-title">Payslip - {{ $payslip->month }} {{ $payslip->year }}</div>

        <table class="info-table">
            <tr>
                <td><span class="label">Employee Name:</span> {{ $payslip->employee->name }}</td>
                {{-- ✅ RESTORED: Direct Property Access --}}
                <td><span class="label">Designation:</span> {{ $payslip->employee->designation ?? '-' }}</td>
            </tr>
            <tr>
                <td><span class="label">Employee ID:</span> {{ $payslip->employee->employee_number }}</td>
                {{-- ✅ RESTORED: Direct Property Access --}}
                <td><span class="label">Department:</span> {{ $payslip->employee->department ?? '-' }}</td>
            </tr>
            <tr>
                <td><span class="label">CNIC:</span> {{ $payslip->employee->cnic ?? '-' }}</td>
                <td><span class="label">Joining Date:</span> {{ \Carbon\Carbon::parse($payslip->employee->joining_date)->format('d M, Y') }}</td>
            </tr>
        </table>

        @php
            $earnings = $payslip->prepared_earnings ?? [];
            $deductions = $payslip->prepared_deductions ?? [];
            $maxRows = max(count($earnings), count($deductions));
            $earningKeys = array_keys($earnings);
            $deductionKeys = array_keys($deductions);
        @endphp

        <table class="salary-table">
            <thead>
                <tr>
                    <th colspan="2">Earnings</th>
                    <th colspan="2">Deductions</th>
                </tr>
                <tr>
                    <th>Description</th>
                    <th class="amount-col">Amount (PKR)</th>
                    <th>Description</th>
                    <th class="amount-col last-col">Amount (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $maxRows; $i++)
                    <tr>
                        <td>{{ $earningKeys[$i] ?? '' }}</td>
                        <td class="amount-col">
                            {{ isset($earningKeys[$i]) ? number_format($earnings[$earningKeys[$i]]) : '' }}
                        </td>
                        <td>{{ $deductionKeys[$i] ?? '' }}</td>
                        <td class="amount-col last-col">
                            {{ isset($deductionKeys[$i]) ? number_format($deductions[$deductionKeys[$i]]) : '' }}
                        </td>
                    </tr>
                @endfor
                
                @if(($payslip->arrears_adjustment ?? 0) > 0)
                <tr>
                    <td class="text-danger">Arrears / Adjustment</td>
                    <td class="amount-col text-danger">{{ number_format($payslip->arrears_adjustment) }}</td>
                    <td></td><td class="amount-col last-col"></td>
                </tr>
                @endif
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Gross Earnings</td>
                <td class="amount">{{ number_format(($payslip->gross_salary ?? 0) + max(0, $payslip->arrears_adjustment ?? 0)) }}</td>
                <td style="border-left: 1px solid #ccc;">Total Deductions</td>
                <td class="amount">{{ number_format($payslip->total_deductions_display ?? 0) }}</td>
            </tr>
        </table>

        <div class="payment-summary">
            <div class="payment-header">Payment Summary</div>
            <div class="payment-body">
                <div class="pay-item">
                    <span class="pay-label">Net Payable</span>
                    <span class="pay-value text-primary">{{ number_format($payslip->payable_amount ?? 0) }}</span>
                </div>
                <div class="pay-item" style="border-left: 1px solid #eee; border-right: 1px solid #eee;">
                    <span class="pay-label">Amount Paid</span>
                    <span class="pay-value text-success">{{ number_format($payslip->paid_amount ?? 0) }}</span>
                </div>
                <div class="pay-item">
                    <span class="pay-label">Balance Due</span>
                    <span class="pay-value text-danger">{{ number_format(($payslip->payable_amount ?? 0) - ($payslip->paid_amount ?? 0)) }}</span>
                </div>
            </div>
            <div class="amount-words">
                <strong>Amount Paid in Words:</strong> {{ $payslip->net_salary_in_words ?? '-' }} Only.
            </div>
        </div>

        @if(!empty($payslip->fund_balances))
        <div class="funds-box">
            <div class="funds-title">Accumulated Fund Balances (As of {{ $payslip->month }} {{ $payslip->year }})</div>
            <div class="funds-grid">
                @foreach($payslip->fund_balances as $fundName => $balance)
                    <div class="fund-item">
                        {{ $fundName }}: <span class="fund-amount">PKR {{ number_format($balance) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="footer">
            System Generated Document | Developed by Your Company <br>
            Printed on {{ now()->format('d M, Y h:i A') }}
        </div>
    </div>
    @endforeach

</body>
</html>