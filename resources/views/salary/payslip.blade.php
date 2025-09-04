<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payslip->employee->name }} - {{ $payslip->month }}, {{ $payslip->year }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .payslip-container { background: #fff; max-width: 800px; margin: 30px auto; padding: 30px; box-shadow: 0 0 15px rgba(0,0,0,.05); border-radius: 8px; }
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
            .payslip-container { box-shadow: none; margin: 0; padding: 0; border-radius: 0; max-width: 100%; }
            body { -webkit-print-color-adjust: exact; } /* Ensures colors print */
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="text-right mb-4 no-print">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print Payslip</button>
        </div>

        <div class="payslip-header row align-items-center">
            <div class="col-6">
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Logo" style="max-height: 60px;">
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
</body>
</html>