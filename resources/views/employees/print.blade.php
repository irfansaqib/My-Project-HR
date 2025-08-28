<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Form - {{ $employee->name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; font-size: 10pt; color: #000; }
        .print-container { max-width: 100%; width: 210mm; margin: auto; padding: 10mm; }
        .header-logo { max-height: 80px; max-width: 150px; }
        .profile-photo { width: 120px; height: 120px; object-fit: cover; border: 1px solid #dee2e6; }
        .section-title { background-color: #f2f2f2 !important; padding: 5px 10px; font-weight: bold; margin-top: 1.5rem; border: 1px solid #ddd; font-size: 12pt; }
        .table-borderless td, .table-borderless th { border: 0; padding: .25rem; }
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
                <h3>{{ $business->business_name }}</h3>
                <p class="mb-0 text-muted">{{ $business->address }}</p>
                <p class="mb-0 text-muted">Phone: {{ $business->phone_number }} | Email: {{ $business->email }}</p>
            </div>
            <div>
                @if($business->logo_path)
                    <img src="{{ asset('storage/' . $business->logo_path) }}" alt="Business Logo" class="header-logo">
                @endif
            </div>
        </div>

        <h2 class="text-center mb-4" style="text-decoration: underline;">Employee Information Form</h2>

        <div class="section-title">Personal Information</div>
        <div class="row mt-3">
            <div class="col-8">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr><td style="width: 30%;"><strong>Full Name:</strong></td><td>{{ $employee->name }}</td></tr>
                        <tr><td><strong>Father's Name:</strong></td><td>{{ $employee->father_name ?? 'N/A' }}</td></tr>
                        <tr><td><strong>CNIC:</strong></td><td>{{ $employee->cnic }}</td></tr>
                        <tr><td><strong>Date of Birth:</strong></td><td>{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M, Y') : 'N/A' }}</td></tr>
                        <tr><td><strong>Gender:</strong></td><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
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
                <tr><td style="width: 25%;"><strong>Phone Number:</strong></td><td>{{ $employee->phone }}</td></tr>
                <tr><td><strong>Email Address:</strong></td><td>{{ $employee->email }}</td></tr>
                <tr><td><strong>Address:</strong></td><td>{{ $employee->address ?? 'N/A' }}</td></tr>
            </tbody>
        </table>

        <div class="section-title">Emergency Contact</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr><td style="width: 25%;"><strong>Contact Person:</strong></td><td>{{ $employee->emergency_contact_name ?? 'N/A' }}</td></tr>
                <tr><td><strong>Relation:</strong></td><td>{{ $employee->emergency_contact_relation ?? 'N/A' }}</td></tr>
                <tr><td><strong>Phone Number:</strong></td><td>{{ $employee->emergency_contact_phone ?? 'N/A' }}</td></tr>
            </tbody>
        </table>

        <div class="section-title">Employment Details</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr><td style="width: 25%;"><strong>Employee ID:</strong></td><td>{{ $employee->employee_number }}</td></tr>
                <tr><td><strong>Designation:</strong></td><td>{{ $employee->designation }}</td></tr>
                <tr><td><strong>Department:</strong></td><td>{{ $employee->department ?? 'N/A' }}</td></tr>
                <tr><td><strong>Date of Joining:</strong></td><td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</td></tr>
            </tbody>
        </table>

        <div class="section-title">Qualifications</div>
        <table class="table table-sm table-bordered mt-2">
            <thead><tr><th>Degree / Title</th><th>Institute</th><th>Year</th></tr></thead>
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
            <thead><tr><th>Company</th><th>Job Title</th><th>Period</th></tr></thead>
            <tbody>
                @forelse($employee->experiences as $exp)
                <tr><td>{{ $exp->company_name }}</td><td>{{ $exp->job_title }}</td><td>{{ \Carbon\Carbon::parse($exp->from_date)->format('M Y') }} to {{ \Carbon\Carbon::parse($exp->to_date)->format('M Y') }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">No experience listed.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Salary Details</div>
        <table class="table table-sm table-borderless mt-2">
            <tbody>
                <tr>
                    <td style="width: 25%;"><strong>Basic Salary:</strong></td><td>{{ number_format($employee->basic_salary, 2) }}</td>
                    <td style="width: 25%;"><strong>House Rent:</strong></td><td>{{ number_format($employee->house_rent, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Utilities:</strong></td><td>{{ number_format($employee->utilities, 2) }}</td>
                    <td><strong>Medical:</strong></td><td>{{ number_format($employee->medical, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Conveyance:</strong></td><td>{{ number_format($employee->conveyance, 2) }}</td>
                    <td><strong>Other Allowance:</strong></td><td>{{ number_format($employee->other_allowance, 2) }}</td>
                </tr>
                <tr class="border-top">
                    <td><strong>Total Salary:</strong></td><td><strong>{{ number_format($employee->total_salary, 2) }}</strong></td>
                    <td></td><td></td>
                </tr>
            </tbody>
        </table>
        
        <div class="section-title">Annual Leave Allocation</div>
        <table class="table table-sm table-borderless mt-2">
             <tbody>
                <tr>
                    <td style="width: 25%;"><strong>Leave Period:</strong></td>
                    <td colspan="3">{{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Annual Leaves:</strong></td><td>{{ $employee->leaves_annual }}</td>
                    <td><strong>Sick Leaves:</strong></td><td>{{ $employee->leaves_sick }}</td>
                </tr>
                <tr>
                    <td><strong>Casual Leaves:</strong></td><td>{{ $employee->leaves_casual }}</td>
                    <td><strong>Other Leaves:</strong></td><td>{{ $employee->leaves_other }}</td>
</tr>
                <tr class="border-top">
                    <td><strong>Total Leaves:</strong></td><td><strong>{{ $employee->total_leaves }}</strong></td>
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