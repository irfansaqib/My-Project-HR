<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Tax Certificates</title>
    <style>
        body { font-family: sans-serif; padding: 0; margin: 0; font-size: 12px; }
        .page-container { 
            width: 100%; max-width: 800px; margin: 0 auto; padding: 40px; 
            page-break-after: always; position: relative; height: 95vh; 
        }
        .no-print { text-align: center; padding: 15px; background: #f4f4f4; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        
        /* Certificate Styles */
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .legal-name { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .sub-title { font-size: 16px; margin-top: 5px; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 60px; }

        @media print {
            .no-print { display: none; }
            .page-container { border: none; margin: 0; width: 100%; height: auto; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #333; color: #fff; border: none; cursor: pointer; border-radius: 4px;">Print All Certificates</button>
    </div>

    @foreach($certificates as $data)
    <div class="page-container">
        <div class="header">
            <div class="legal-name">{{ $data['business']->legal_name ?? $data['business']->name }}</div>
            <div class="sub-title">Annual Salary & Tax Deduction Certificate</div>
            <div>Financial Year: {{ $data['fy'] }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <strong>Employee:</strong> {{ $data['employee']->name }}<br>
                    <strong>CNIC:</strong> {{ $data['employee']->cnic }}<br>
                    <strong>Designation:</strong> {{ $data['designation'] }}
                </td>
                <td width="40%" align="right">
                    <strong>Issue Date:</strong> {{ $data['date'] }}<br>
                    <strong>Period:</strong> {{ $data['periodText'] }}
                </td>
            </tr>
        </table>

        <p>This is to certify that the sum of <strong>PKR {{ number_format($data['totalTax']) }}</strong> has been deducted as Income Tax from the salary of the above-mentioned employee for the period <strong>{{ $data['periodText'] }}</strong> details of which are as follows:</p>

        <table class="table">
            <thead>
                <tr style="background: #eee;"><th>Month</th><th class="text-right">Gross Salary</th><th class="text-right">Tax Deducted</th></tr>
            </thead>
            <tbody>
                @foreach($data['items'] as $item)
                <tr>
                    <td>{{ $item->salarySheet->month->format('F, Y') }}</td>
                    <td class="text-right">{{ number_format($item->gross_salary) }}</td>
                    <td class="text-right">{{ number_format($item->income_tax) }}</td>
                </tr>
                @endforeach
                <tr style="background: #eee; font-weight: bold;">
                    <td>Total</td>
                    <td class="text-right">{{ number_format($data['totalIncome']) }}</td>
                    <td class="text-right">{{ number_format($data['totalTax']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <div style="border-top: 1px solid #000; width: 200px; text-align: center;">Authorized Signatory</div>
        </div>
    </div>
    @endforeach

</body>
</html>