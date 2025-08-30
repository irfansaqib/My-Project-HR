<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employment Contract - {{ $employee->name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; font-size: 11pt; color: #000; line-height: 1.6; }
        .contract-container { max-width: 100%; width: 210mm; margin: auto; padding: 10mm; }
        .header { text-align: center; margin-bottom: 30px; }
        h1, h2, h3 { margin-top: 20px; margin-bottom: 10px; }
        h1 { font-size: 18pt; text-transform: uppercase; font-weight: bold; }
        h2 { font-size: 14pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px; }
        p { text-align: justify; }
        .signature-area { margin-top: 80px; page-break-inside: avoid; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="contract-container">
        <div class="text-right mb-4 no-print">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print Contract</button>
        </div>

        <div class="header">
            <h1>Employment Contract</h1>
        </div>

        <p>This Employment Contract (hereinafter referred to as the "Agreement") is made and entered into on this <strong>{{ \Carbon\Carbon::now()->format('jS \d\a\y \o\f F, Y') }}</strong>.</p>

        <h2>BETWEEN:</h2>
        <p><strong>{{ $business->legal_name }}</strong>, a company organized and existing under the laws of Pakistan, with its principal office located at {{ $business->address }} (hereinafter referred to as the "Employer").</p>

        <h2>AND:</h2>
        <p><strong>{{ $employee->name }}</strong>, son/daughter of {{ $employee->father_name ?? '[Father\'s Name]' }}, holding CNIC No. <strong>{{ $employee->cnic }}</strong>, residing at {{ $employee->address ?? '[Employee Address]' }} (hereinafter referred to as the "Employee").</p>

        <p>The Employer and the Employee are hereinafter collectively referred to as the "Parties".</p>
        
        <h2>1. Position and Duties</h2>
        <p>The Employer hereby employs the Employee in the capacity of <strong>{{ $employee->designation }}</strong> within the <strong>{{ $employee->department ?? '[Department]' }}</strong> department. The Employee's duties and responsibilities shall include, but are not limited to, the following:</p>
        <div style="padding-left: 20px; border-left: 2px solid #eee;">
            <p>{!! nl2br(e($employee->job_description ?? 'As assigned by the management from time to time.')) !!}</p>
        </div>

        <h2>2. Date of Commencement and Probation</h2>
        <p>The employment shall commence on <strong>{{ \Carbon\Carbon::parse($employee->joining_date)->format('F j, Y') }}</strong>. The first <strong>{{ $employee->probation_period ?? 3 }} months</strong> of employment shall be a probationary period, during which either party may terminate this Agreement with one (1) week's notice.</p>

        <h2>3. Remuneration</h2>
        <p>The Employer shall pay the Employee a total gross monthly salary of <strong>PKR {{ number_format($employee->total_salary, 2) }}/-</strong> (Rupees {{ (new \NumberFormatter('en_US', \NumberFormatter::SPELLOUT))->format($employee->total_salary) }} only), subject to applicable taxes and deductions as required by law.</p>

        <h2>4. Working Hours & Leave</h2>
        <p>The standard working hours shall be as per company policy. The Employee shall be entitled to annual, sick, and casual leaves as per the prevailing labour laws of Pakistan and company policy.</p>

        <h2>5. Confidentiality</h2>
        <p>The Employee agrees to keep all company information, including but not limited to business operations, client lists, and financial data, strictly confidential during and after the term of employment.</p>

        <h2>6. Termination</h2>
        <p>Subsequent to the confirmation of employment, this Agreement may be terminated by either party by giving one (1) month's written notice or payment of one month's salary in lieu of notice.</p>

        <h2>7. Governing Law</h2>
        <p>This Agreement shall be governed by and construed in accordance with the laws of the Islamic Republic of Pakistan.</p>

        <p><strong>IN WITNESS WHEREOF</strong>, the Parties have executed this Agreement as of the date first above written.</p>

        <div class="row signature-area">
            <div class="col-6">
                <hr style="width: 80%; border-top: 1px solid #000;">
                <p class="text-center"><strong>For: {{ $business->legal_name }}</strong><br>(Employer)</p>
            </div>
            <div class="col-6">
                <hr style="width: 80%; border-top: 1px solid #000;">
                <p class="text-center"><strong>{{ $employee->name }}</strong><br>(Employee)</p>
            </div>
        </div>
        <p class="text-center text-muted mt-5"><small>-- End of Agreement --</small></p>
    </div>
</body>
</html>