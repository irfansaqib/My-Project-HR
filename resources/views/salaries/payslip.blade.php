<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payslip->employee->name }} - {{ \Carbon\Carbon::create()->month($payslip->month)->format('F') }}, {{ $payslip->year }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .payslip-container { background: #fff; width: 210mm; margin: 20px auto; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,.1); }
        .payslip-header { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .table-sm td, .table-sm th { padding: .4rem; }
        @media print {
            body { background-color: #fff; }
            .no-print { display: none !important; }
            .payslip-container { box-shadow: none; margin: 0; padding: 5mm; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="text-right mb-4 no-print">
             <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Payslip</button>
        </div>

        <div class="payslip-header d-flex justify-content-between align-items-center">
            <div>
                <h4>{{ $business->name }}</h4>
                <p class="mb-0 text-muted">{{ $business->address }}</p>
            </div>
            <div>
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Logo" style="max-height: 70px;">
                @endif
            </div>
        </div>

        <div class="text-center mb-4">
            <h5>Payslip for the month of {{ \Carbon\Carbon::create()->month($payslip->month)->format('F') }}, {{ $payslip->year }}</h5>
        </div>

        <table class="table table-sm table-bordered mb-4">
            <tbody>
                <tr>
                    <th style="width:20%;">Employee Name</th><td style="width:30%;">{{ $payslip->employee->name }}</td>
                    <th style="width:20%;">Employee ID</th><td style="width:30%;">{{ $payslip->employee->employee_number }}</td>
                </tr>
                <tr>
                    <th>Designation</th><td>{{ $payslip->employee->designation }}</td>
                    <th>Department</th><td>{{ $payslip->employee->department }}</td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-6">
                <h5>Earnings</h5>
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-right">{{ number_format($payslip->basic_salary, 2) }}</td>
                        </tr>
                        @foreach($payslip->allowances_breakdown as $name => $amount)
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ number_format($amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td>Gross Earnings</td>
                            <td class="text-right">{{ number_format($payslip->gross_salary, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-6">
                <h5>Deductions</h5>
                <table class="table table-sm table-striped">
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
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td>Total Deductions</td>
                            <td class="text-right">{{ number_format($payslip->total_deductions + $payslip->income_tax, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="mt-4 p-3 bg-secondary text-white rounded">
            <h4 class="d-flex justify-content-between">
                <span>Net Salary Paid:</span>
                <span>PKR {{ number_format($payslip->net_salary, 2) }}</span>
            </h4>
        </div>
        <p class="text-center mt-3 text-muted"><small>This is a computer-generated payslip and does not require a signature.</small></p>
    </div>
</body>
</html>