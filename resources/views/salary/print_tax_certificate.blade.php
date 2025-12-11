<!DOCTYPE html>
<html>
<head><title>Tax Certificate</title>
<style>
    body { font-family: sans-serif; padding: 30px; font-size: 13px; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
    .legal-name { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0; }
    .sub-title { font-size: 16px; margin-top: 5px; }
    .info-table { width: 100%; margin-bottom: 30px; }
    .info-table td { padding: 5px; vertical-align: top; }
    .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
    .text-right { text-align: right; }
    .footer { margin-top: 60px; }
</style>
</head>
<body>
    <div class="header">
        {{-- ✅ Legal Name --}}
        <div class="legal-name">{{ $business->legal_name ?? $business->name }}</div>
        <div class="sub-title">Annual Salary & Tax Deduction Certificate</div>
        <div>Financial Year: {{ $fy }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Employee:</strong> {{ $employee->name }}<br>
                <strong>CNIC:</strong> {{ $employee->cnic }}<br>
                <strong>Designation:</strong> {{ $designation }}
            </td>
            <td width="40%" align="right">
                <strong>Issue Date:</strong> {{ $date }}<br>
                <strong>Period:</strong> {{ $periodText }}
            </td>
        </tr>
    </table>

    <p>This is to certify that sum of <strong>PKR {{ number_format($totalTax) }}</strong> has been deducted as Income Tax from the salary of the above employee.</p>

    <table class="table">
        <thead>
            <tr style="background: #eee;"><th>Month</th><th class="text-right">Gross Salary</th><th class="text-right">Tax Deducted</th></tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->salarySheet->month->format('F, Y') }}</td>
                <td class="text-right">{{ number_format($item->gross_salary) }}</td>
                <td class="text-right">{{ number_format($item->income_tax) }}</td>
            </tr>
            @endforeach
            <tr style="background: #eee; font-weight: bold;">
                <td>Total</td>
                <td class="text-right">{{ number_format($totalIncome) }}</td>
                <td class="text-right">{{ number_format($totalTax) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div style="border-top: 1px solid #000; width: 200px; text-align: center;">Authorized Signatory</div>
    </div>
</body>
</html>