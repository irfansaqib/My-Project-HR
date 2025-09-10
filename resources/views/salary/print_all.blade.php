<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Payslips - {{ $payslips->first()->month }}, {{ $payslips->first()->year }}</title>
    <style>
        /*!
         * Bootstrap v4.5.2 (https://getbootstrap.com/)
         * Copyright 2011-2020 The Bootstrap Authors
         * Copyright 2011-2020 Twitter, Inc.
         * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
         */
        :root{--blue:#007bff;--indigo:#6610f2;--purple:#6f42c1;--pink:#e83e8c;--red:#dc3545;--orange:#fd7e14;--yellow:#ffc107;--green:#28a745;--teal:#20c997;--cyan:#17a2b8;--white:#fff;--gray:#6c757d;--gray-dark:#343a40;--primary:#007bff;--secondary:#6c757d;--success:#28a745;--info:#17a2b8;--warning:#ffc107;--danger:#dc3545;--light:#f8f9fa;--dark:#343a40;--breakpoint-xs:0;--breakpoint-sm:576px;--breakpoint-md:768px;--breakpoint-lg:992px;--breakpoint-xl:1200px;--font-family-sans-serif:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-family-monospace:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;}
        *,::after,::before{box-sizing:border-box;}
        html{font-family:sans-serif;line-height:1.15;-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent;}
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";font-size:1rem;font-weight:400;line-height:1.5;color:#212529;text-align:left;background-color:#fff;}
        h3,h4,h5{margin-top:0;margin-bottom:.5rem;}
        p{margin-top:0;margin-bottom:1rem;}
        img{vertical-align:middle;border-style:none;}
        table{border-collapse:collapse;}
        th{text-align:inherit;}
        label{display:inline-block;margin-bottom:.5rem;}
        button{border-radius:0;}
        button:focus{outline:1px dotted;outline:5px auto -webkit-focus-ring-color;}
        button,input,select{margin:0;font-family:inherit;font-size:inherit;line-height:inherit;}
        button,select{text-transform:none;}
        select{word-wrap:normal;}
        [type=button],[type=reset],[type=submit],button{-webkit-appearance:button;}
        .container{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;}
        .row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px;}
        .col-6{position:relative;width:100%;padding-right:15px;padding-left:15px;flex:0 0 50%;max-width:50%;}
        .table{width:100%;margin-bottom:1rem;color:#212529;}
        .table td,.table th{padding:.75rem;vertical-align:top;border-top:1px solid #dee2e6;}
        .table thead th{vertical-align:bottom;border-bottom:2px solid #dee2e6;}
        .table-sm td,.table-sm th{padding:.3rem;}
        .table-bordered{border:1px solid #dee2e6;}
        .table-bordered td,.table-bordered th{border:1px solid #dee2e6;}
        .btn{display:inline-block;font-weight:400;color:#212529;text-align:center;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-color:transparent;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;}
        .btn-primary{color:#fff;background-color:#007bff;border-color:#007bff;}
        .btn-secondary{color:#fff;background-color:#6c757d;border-color:#6c757d;}
        .d-flex{display:flex!important;}
        .justify-content-between{justify-content:space-between!important;}
        .align-items-center{align-items:center!important;}
        .mb-0{margin-bottom:0!important;}
        .mb-3{margin-bottom:1rem!important;}
        .mb-4{margin-bottom:1.5rem!important;}
        .my-3{margin-top:1rem!important;margin-bottom:1rem!important;}
        .mt-2{margin-top:.5rem!important;}
        .mt-4{margin-top:1.5rem!important;}
        .text-right{text-align:right!important;}
        .text-center{text-align:center!important;}
        .text-uppercase{text-transform:uppercase!important;}
        .font-weight-bold{font-weight:700!important;}
        .text-muted{color:#6c757d!important;}
        
        /* Custom Payslip Styles */
        body { background-color: #e9ecef; }
        .payslip-container { background: #fff; max-width: 800px; margin: 30px auto; padding: 30px; box-shadow: 0 0 15px rgba(0,0,0,.05); border-radius: 8px; page-break-after: always; }
        .payslip-header { border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 25px; }
        .payslip-header h4 { font-weight: bold; color: #333; }
        .payslip-title { text-align: center; margin-bottom: 25px; font-weight: 300; color: #007bff; }
        .info-table th { background-color: #f8f9fa; width: 25%; }
        .amount-table thead th { background-color: #f8f9fa; }
        .amount-table .total-row td { font-weight: bold; background-color: #f8f9fa; }
        .net-salary-box { background-color: #007bff; color: #fff; padding: 15px; border-radius: 5px; }
        .net-salary-box h4, .net-salary-box p { margin: 0; }
        .footer-text { text-align: center; margin-top: 20px; color: #888; font-size: 0.8rem; }
        
        @media print {
            body { background-color: #fff; }
            .no-print { display: none !important; }
            .payslip-container { box-shadow: none; margin: 0 auto 30px auto; padding: 0; border-radius: 0; max-width: 100%; border: 1px solid #ddd; padding: 20px; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary">Print All Payslips</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>

    @php
        $logoPath = $business->logo_path ? public_path('storage/' . $business->logo_path) : null;
        $logoData = $logoPath && file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $logoMime = $logoPath && file_exists($logoPath) ? mime_content_type($logoPath) : null;
    @endphp

    @foreach($payslips as $payslip)
        <div class="payslip-container">
            <div class="payslip-header row align-items-center">
                <div class="col-6">
                    @if($logoData)
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo" style="max-height: 60px;">
                    @else
                        <h4 class="mb-0">{{ $business->name }}</h4>
                    @endif
                </div>
                <div class="col-6 text-right">
                    <h4 class="mb-0">{{ $business->name }}</h4>
                    <p class="mb-0 text-muted">{{ $business->address }}</p>
                </div>
            </div>

            <h3 class="payslip-title">Payslip for the month of {{ $payslip->month }}, {{ $payslip->year }}</h3>

            <table class="table table-bordered table-sm mb-4 info-table">
                <tbody>
                    <tr>
                        <th>Employee Name</th><td>{{ $payslip->employee->name }}</td>
                        <th>Employee ID</th><td>{{ $payslip->employee->employee_number }}</td>
                    </tr>
                    <tr>
                        <th>Designation</th><td>{{ $payslip->employee->designation }}</td>
                        <th>Department</th><td>{{ $payslip->employee->department }}</td>
                    </tr>
                     <tr>
                        <th>Joining Date</th><td>{{ \Carbon\Carbon::parse($payslip->employee->joining_date)->format('d M, Y') }}</td>
                        <th>Payment Date</th><td>{{ now()->format('d M, Y') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="row">
                <div class="col-6">
                    <h5 class="mb-3">Earnings</h5>
                    <table class="table table-sm amount-table">
                        <thead><tr><th>Description</th><th class="text-right">Amount (PKR)</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary</td>
                                <td class="text-right">{{ number_format($payslip->employee->basic_salary, 2) }}</td>
                            </tr>
                            @foreach($payslip->allowances_breakdown as $name => $amount)
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="text-right">{{ number_format($amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="total-row">
                            <tr>
                                <td>Gross Earnings</td>
                                <td class="text-right">{{ number_format($payslip->gross_salary, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-6">
                    <h5 class="mb-3">Deductions</h5>
                    <table class="table table-sm amount-table">
                        <thead><tr><th>Description</th><th class="text-right">Amount (PKR)</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Income Tax</td>
                                <td class="text-right">{{ number_format($payslip->income_tax, 2) }}</td>
                            </tr>
                            @foreach($payslip->deductions_breakdown as $name => $amount)
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="text-right">{{ number_format($amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="total-row">
                            <tr>
                                <td>Total Deductions</td>
                                <td class="text-right">{{ number_format($payslip->total_deductions + $payslip->income_tax, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 net-salary-box">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="text-uppercase">Net Salary Paid:</h4>
                    <h4 class="text-uppercase">PKR {{ number_format($payslip->net_salary, 2) }}</h4>
                </div>
            </div>
            <p class="mt-2"><strong>Amount in Words:</strong> {{ $payslip->net_salary_in_words }} Rupees Only.</p>
            
            <p class="footer-text">This is a computer-generated payslip and does not require a signature.</p>
        </div>
    @endforeach
</body>
</html>