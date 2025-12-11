<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Certificates</title>
    <style>
        body { font-family: 'Arial', sans-serif; padding: 0; margin: 0; font-size: 13px; color: #000; }
        .page-container { 
            width: 100%; max-width: 800px; margin: 0 auto; padding: 40px; 
            box-sizing: border-box;
            page-break-after: always; 
        }
        .page-container:last-child { page-break-after: auto; }
        
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .legal-name { font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .sub-title { font-size: 16px; margin-top: 5px; font-weight: bold; text-decoration: underline; }
        
        .info-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .label { font-weight: bold; width: 130px; display: inline-block; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; }
        
        /* HEADERS ARE CENTERED */
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        /* DATA IS RIGHT ALIGNED */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .footer { margin-top: 80px; }
        .sig-box { border-top: 1px solid #000; width: 250px; text-align: center; padding-top: 5px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; padding: 15px; background: #eee; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">Print Certificates</button>
    </div>

    @foreach($certificates as $cert)
    <div class="page-container">
        
        {{-- HEADER --}}
        <div class="header">
            <div class="legal-name">{{ $client->name }}</div>
            <div class="sub-title">Annual Salary & Tax Deduction Certificate</div>
            <div style="margin-top: 5px;"><strong>Financial Year:</strong> {{ $fyLabel }}</div>
        </div>

        {{-- EMPLOYEE INFO --}}
        <table class="info-table">
            <tr>
                <td width="60%">
                    <span class="label">Employee Name:</span> {{ $cert['employee']->name }} <br>
                    <span class="label">CNIC No:</span> {{ $cert['cnic'] }} <br>
                    <span class="label">Designation:</span> {{ $cert['designation'] }}
                </td>
                <td width="40%" align="right">
                    <strong>Issue Date:</strong> {{ $printDate }} <br>
                    <strong>Period:</strong> {{ $cert['period_text'] }}
                </td>
            </tr>
        </table>

        {{-- CERTIFICATION TEXT --}}
        <p style="text-align: justify; line-height: 1.6; margin-bottom: 20px;">
            This is to certify that a sum of <strong>PKR {{ number_format($cert['total_tax']) }}</strong> has been deducted as Income Tax 
            from the salary of the above-mentioned employee under <strong>Section 149</strong> of the Income Tax Ordinance, 2001 
            for the period mentioned above. The details of monthly deductions are as follows:
        </p>

        {{-- DATA TABLE --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th width="35%">Month</th>
                    {{-- CENTERED HEADERS (Default Style) --}}
                    <th>Gross Salary (PKR)</th>
                    <th>Tax Deducted (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cert['items'] as $item)
                <tr>
                    <td>{{ $item['month'] }}</td>
                    <td class="text-right">{{ number_format($item['gross']) }}</td>
                    <td class="text-right">{{ number_format($item['tax']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td class="text-center">Total</td>
                    <td class="text-right">{{ number_format($cert['total_gross']) }}</td>
                    <td class="text-right">{{ number_format($cert['total_tax']) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- FOOTER / SIGNATURE --}}
        <div class="footer">
            <div class="sig-box">
                Authorized Signatory <br>
                <small>{{ $client->name }}</small>
            </div>
        </div>

    </div>
    @endforeach

</body>
</html>