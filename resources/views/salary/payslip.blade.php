<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payslip->employee->name }} - {{ $payslip->month }}, {{ $payslip->year }}</title>
    <style>
        /* Shared styles for both PDF and Screen */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #333; margin: 0; padding: 0; }
        .payslip-container { padding: 20px; }
        .header-table, .info-table, .amount-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo { max-height: 50px; width: auto; }
        .business-name { font-size: 14pt; font-weight: bold; }
        .business-address { font-size: 8pt; color: #555; }
        .payslip-title { text-align: center; font-size: 12pt; font-weight: bold; color: #007bff; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; padding: 8px 0; margin: 20px 0; }
        .info-table { margin-bottom: 20px; }
        .info-table th, .info-table td { border: 1px solid #dee2e6; padding: 5px; }
        .info-table th { background-color: #f8f9fa; width: 20%; font-weight: bold; }
        .info-table td { width: 30%; }
        .amount-section { width: 48%; display: inline-block; vertical-align: top; }
        .earnings-section { float: left; }
        .deductions-section { float: right; }
        .amount-table th, .amount-table td { padding: 5px; }
        .amount-table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: bold; }
        .amount-table td { border-bottom: 1px solid #eee; }
        .amount-table .empty-row td { border-bottom: 1px solid #fff; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .net-salary-box { background-color: #007bff !important; color: #fff !important; padding: 10px; margin-top: 20px; -webkit-print-color-adjust: exact; }
        .net-salary-box-table { width: 100%; }
        .net-salary-box-table td { color: #fff; font-size: 11pt; font-weight: bold; }
        .amount-in-words { margin-top: 10px; }
        .footer-text { text-align: center; margin-top: 20px; color: #888; font-size: 8pt; }
        .clearfix::after { content: ""; clear: both; display: table; }
        
        /* Styles for browser view only */
        @media screen {
            body { background-color: #e9ecef; }
            .payslip-container { max-width: 800px; margin: 30px auto; box-shadow: 0 0 15px rgba(0,0,0,.05); border-radius: 8px; background-color: #fff; }
            .no-print { display: block; text-align: right; margin-bottom: 20px; }
            .btn{display:inline-block;font-weight:400;color:#212529;text-align:center;vertical-align:middle;background-color:transparent;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;}
            .btn-primary{color:#fff;background-color:#007bff;border-color:#007bff;}
            .btn-secondary{color:#fff;background-color:#6c757d;border-color:#6c757d;}
        }
        
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        {{-- ** THIS IS THE FIX: This section will only show if it's NOT a PDF ** --}}
        @if(!isset($isPdf) || !$isPdf)
        <div class="no-print">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print Payslip</button>
        </div>
        @endif

        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    @php
                        $logoPath = $business->logo_path ? public_path('storage/' . $business->logo_path) : null;
                        $logoData = $logoPath && file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
                        $logoMime = $logoPath && file_exists($logoPath) ? mime_content_type($logoPath) : null;
                    @endphp
                    @if($logoData)
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo" class="logo">
                    @else
                        <span class="business-name">{{ $business->name }}</span>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <span class="business-name">{{ $business->name }}</span><br>
                    <span class="business-address">{{ $business->address }}</span>
                </td>
            </tr>
        </table>

        <div class="payslip-title">Payslip for the month of {{ $payslip->month }}, {{ $payslip->year }}</div>

        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $payslip->employee->name }}</td>
                <th>Employee ID</th>
                <td>{{ $payslip->employee->employee_number }}</td>
            </tr>
            <tr>
                <th>Designation</th>
                <td>{{ $payslip->employee->designation }}</td>
                <th>Department</th>
                <td>{{ $payslip->employee->department }}</td>
            </tr>
            <tr>
                <th>Joining Date</th>
                <td>{{ \Carbon\Carbon::parse($payslip->employee->joining_date)->format('d M, Y') }}</td>
                <th>Payment Date</th>
                <td>{{ now()->format('d M, Y') }}</td>
            </tr>
        </table>

        @php
            $earningsCount = 1 + count($payslip->allowances_breakdown);
            $deductionsCount = 1 + count($payslip->deductions_breakdown);
        @endphp

        <div class="clearfix">
            <div class="amount-section earnings-section">
                <table class="amount-table">
                    <thead><tr><th>Earnings</th><th class="text-right">Amount (PKR)</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-right">{{ number_format($payslip->employee->basic_salary, 0) }}</td>
                        </tr>
                        @foreach($payslip->allowances_breakdown as $name => $amount)
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ number_format($amount, 0) }}</td>
                        </tr>
                        @endforeach
                        @for ($i = $earningsCount; $i < $deductionsCount; $i++)
                            <tr class="empty-row"><td>&nbsp;</td><td>&nbsp;</td></tr>
                        @endfor
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td>Gross Earnings</td>
                            <td class="text-right">{{ number_format($payslip->gross_salary, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="amount-section deductions-section">
                <table class="amount-table">
                    <thead><tr><th>Deductions</th><th class="text-right">Amount (PKR)</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>Income Tax</td>
                            <td class="text-right">{{ number_format($payslip->income_tax, 0) }}</td>
                        </tr>
                        @foreach($payslip->deductions_breakdown as $name => $amount)
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ number_format($amount, 0) }}</td>
                        </tr>
                        @endforeach
                        @for ($i = $deductionsCount; $i < $earningsCount; $i++)
                            <tr class="empty-row"><td>&nbsp;</td><td>&nbsp;</td></tr>
                        @endfor
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td>Total Deductions</td>
                            <td class="text-right">{{ number_format($payslip->total_deductions + $payslip->income_tax, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="net-salary-box">
            <table class="net-salary-box-table">
                <tr>
                    <td>NET SALARY PAID:</td>
                    <td class="text-right">PKR {{ number_format($payslip->net_salary, 0) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="amount-in-words">
            <strong>Amount in Words:</strong> {{ \App\Helpers\NumberHelper::numberToWords(round($payslip->net_salary)) }} Rupees Only.
        </div>
        
        <p class="footer-text">This is a computer-generated payslip and does not require a signature.</p>
    </div>
</body>
</html>