<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Form - {{ $employee->name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; font-size: 10pt; color: #000; }
        .print-container { max-width: 100%; width: 210mm; margin: auto; padding: 10mm; }
        .header-logo { max-height: 80px; }
        .profile-photo { width: 120px; height: 140px; object-fit: cover; border: 1px solid #dee2e6; }
        .section-title { background-color: #e0e7ebf6 !important; padding: 5px 10px; font-weight: bold; margin-top: 1.5rem; border: 1px solid #ddd; font-size: 12pt; }
        .table-borderless td, .table-borderless th { border: 0; padding: .25rem; }
        .table td, .table th { padding: .4rem .75rem; vertical-align: middle; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="text-right mb-4 no-print">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print</button>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h3>{{ $business->legal_name }}</h3>
                <p class="mb-0 text-muted">{{ $business->address }}</p>
                <p class="mb-0 text-muted">Phone: {{ $business->phone_number }} | Email: {{ $business->email }}</p>
            </div>
            <div class="text-right">
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Business Logo" class="header-logo">
                @endif
            </div>
        </div>

        <h2 class="text-center mb-4" style="text-transform: uppercase; font-weight: bold;">Employee Information Form</h2>

        <div class="section-title">Personal Information</div>
        <div class="row mt-3">
            <div class="col-8">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr><td style="width: 30%;" class="text-muted">Full Name</td><td><strong>{{ $employee->name }}</strong></td></tr>
                        <tr><td class="text-muted">Father's Name</td><td><strong>{{ $employee->father_name ?? 'N/A' }}</strong></td></tr>
                        <tr><td class="text-muted">CNIC</td><td><strong>{{ $employee->cnic }}</strong></td></tr>
                        <tr><td class="text-muted">Date of Birth</td><td><strong>{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M, Y') : 'N/A' }}</strong></td></tr>
                        <tr><td class="text-muted">Gender</td><td><strong>{{ $employee->gender ?? 'N/A' }}</strong></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-4 text-center">
                <img src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/120' }}" alt="Employee Photo" class="profile-photo">
            </div>
        </div>
        
        <div class="section-title">Contact Details</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr><td style="width: 25%;" class="text-muted">Phone Number</td><td><strong>{{ $employee->phone }}</strong></td></tr>
                <tr><td class="text-muted">Email Address</td><td><strong>{{ $employee->email }}</strong></td></tr>
                <tr><td class="text-muted">Address</td><td><strong>{{ $employee->address ?? 'N/A' }}</strong></td></tr>
            </tbody>
        </table>

        <div class="section-title">Emergency Contact</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr><td style="width: 25%;" class="text-muted">Contact Person</td><td><strong>{{ $employee->emergency_contact_name ?? 'N/A' }}</strong></td></tr>
                <tr><td class="text-muted">Relation</td><td><strong>{{ $employee->emergency_contact_relation ?? 'N/A' }}</strong></td></tr>
                <tr><td class="text-muted">Phone Number</td><td><strong>{{ $employee->emergency_contact_phone ?? 'N/A' }}</strong></td></tr>
            </tbody>
        </table>

        <div class="section-title">Employment Details</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr><td style="width: 25%;" class="text-muted">Employee ID</td><td><strong>{{ $employee->employee_number }}</strong></td></tr>
                <tr><td class="text-muted">Designation</td><td><strong>{{ $employee->designation }}</strong></td></tr>
                <tr><td class="text-muted">Department</td><td><strong>{{ $employee->department ?? 'N/A' }}</strong></td></tr>
                <tr><td class="text-muted">Date of Joining</td><td><strong>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</strong></td></tr>
            </tbody>
        </table>

        <div class="section-title">Qualifications</div>
        <table class="table table-sm table-bordered mt-2">
            <thead class="table-secondary"><tr><th>Degree / Title</th><th>Institute</th><th>Year</th></tr></thead>
            <tbody>
                @forelse($employee->qualifications as $qual)
                <tr><td>{{ $qual->degree_title }}</td><td>{{ $qual->institute }}</td><td>{{ $qual->year_of_passing }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">No qualifications listed.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Work Experience</div>
        <table class="table table-sm table-bordered mt-2">
            <thead class="table-secondary"><tr><th>Company</th><th>Job Title</th><th>Period</th></tr></thead>
            <tbody>
                @forelse($employee->experiences as $exp)
                <tr><td>{{ $exp->company_name }}</td><td>{{ $exp->job_title }}</td><td>{{ \Carbon\Carbon::parse($exp->from_date)->format('M Y') }} to {{ \Carbon\Carbon::parse($exp->to_date)->format('M Y') }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">No experience listed.</td></tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="section-title">Salary Details</div>
        <table class="table table-sm table-bordered mt-2">
             <tr class="bg-light"><th style="width:75%">Basic Salary</th><td class="text-right">{{ number_format($employee->basic_salary, 2) }}</td></tr>
            @foreach($employee->salaryComponents->where('type', 'allowance') as $component)
                <tr><td>{{ $component->name }}</td><td class="text-right">{{ number_format($component->pivot->amount, 2) }}</td></tr>
            @endforeach
            <tr class="font-weight-bold" style="background-color: #e9ecef !important;"><td>Gross Salary</td><td class="text-right">{{ number_format($employee->gross_salary, 2) }}</td></tr>
            @foreach($employee->salaryComponents->where('type', 'deduction') as $component)
                <tr><td>{{ $component->name }}</td><td class="text-right text-danger">({{ number_format($component->pivot->amount, 2) }})</td></tr>
            @endforeach
            <tr class="font-weight-bold text-white" style="background-color: #343a40 !important;"><td>Net Salary</td><td class="text-right">{{ number_format($employee->net_salary, 2) }}</td></tr>
        </table>

        <div class="section-title">Bank Account Details</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr>
                    <td style="width: 25%;" class="text-muted">Account Title</td><td><strong>{{ $employee->bank_account_title ?? 'N/A' }}</strong></td>
                    <td style="width: 25%;" class="text-muted">Account Number</td><td><strong>{{ $employee->bank_account_number ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Bank Name</td><td><strong>{{ $employee->bank_name ?? 'N/A' }}</strong></td>
                    <td class="text-muted">Branch</td><td><strong>{{ $employee->bank_branch ?? 'N/A' }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="section-title">Leaves Allocation</div>
        <table class="table table-sm table-borderless mt-2">
             <tbody>
                <tr>
                    <td style="width: 25%;" class="text-muted">Leaves Period</td>
                    <td colspan="3"><strong>{{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Annual Leaves</td><td>{{ $employee->leaves_annual ?? 0 }}</td>
                    <td class="text-muted">Sick Leaves</td><td>{{ $employee->leaves_sick ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Casual Leaves</td><td>{{ $employee->leaves_casual ?? 0 }}</td>
                    <td class="text-muted">Other Leaves</td><td>{{ $employee->leaves_other ?? 0 }}</td>
                </tr>
                <tr class="border-top">
                    <td class="font-weight-bold">Total Leaves</td><td><strong>{{ $employee->leaves_annual + $employee->leaves_sick + $employee->leaves_casual + $employee->leaves_other }}</strong></td>
                    <td></td><td></td>
                </tr>
            </tbody>
        </table>
        
        <div class="row" style="margin-top: 100px; page-break-inside: avoid;">
            <div class="col-6 text-center">
                <hr class="mx-auto" style="width: 70%;">
                <p>Employee Signature</p>
            </div>
            <div class="col-6 text-center">
                <hr class="mx-auto" style="width: 70%;">
                <p>Management / HR Signature</p>
            </div>
        </div>
    </div>
</body>
</html>