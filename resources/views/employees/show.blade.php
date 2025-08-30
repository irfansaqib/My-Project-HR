@extends('layouts.admin')

@section('title', 'Employee Profile')

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Left Column for Profile Picture and Basic Info --}}
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/128' }}"
                         alt="User profile picture" style="height: 128px; width: 128px; object-fit: cover;">
                </div>
                <h3 class="profile-username text-center">{{ $employee->name }}</h3>
                <p class="text-muted text-center">{{ $employee->designation }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Employee ID</b> <a class="float-right">{{ $employee->employee_number }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right"><span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($employee->status) }}</span></a>
                    </li>
                </ul>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-block"><b>Edit Profile</b></a>
                <a href="{{ route('employees.contract', $employee) }}" class="btn btn-info btn-block"><b>Print Contract</b></a>
                <a href="{{ route('employees.print', $employee) }}" class="btn btn-secondary btn-block"><b>Print Form</b></a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        {{-- Right Column for Tabs with Detailed Info --}}
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab">Personal Information</a></li>
                    <li class="nav-item"><a class="nav-link" href="#employment" data-toggle="tab">Employment Details</a></li>
                    <li class="nav-item"><a class="nav-link" href="#prof_details" data-toggle="tab">Qualification & Experience</a></li>
                    <li class="nav-item"><a class="nav-link" href="#salary" data-toggle="tab">Salary & Leaves</a></li>
                    <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank Details</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    {{-- Tab 1: Personal Information --}}
                    <div class="tab-pane active" id="personal">
                        <table class="table table-sm table-hover" style="width: 100%;">
                            <tbody>
                                <tr><td style="width: 25%;"><strong>Full Name</strong></td><td>{{ $employee->name }}</td></tr>
                                <tr><td><strong>Father's Name</strong></td><td>{{ $employee->father_name ?? 'N/A' }}</td></tr>
                                <tr><td><strong>CNIC</strong></td><td>{{ $employee->cnic }}</td></tr>
                                <tr><td><strong>Date of Birth</strong></td><td>{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M, Y') : 'N/A' }}</td></tr>
                                <tr><td><strong>Gender</strong></td><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                                <tr class="table-secondary"><td colspan="2" class="font-weight-bold">Contact Information</td></tr>
                                <tr><td><strong>Phone Number</strong></td><td>{{ $employee->phone }}</td></tr>
                                <tr><td><strong>Email Address</strong></td><td>{{ $employee->email }}</td></tr>
                                <tr><td><strong>Address</strong></td><td>{{ $employee->address ?? 'N/A' }}</td></tr>
                                <tr class="table-secondary"><td colspan="2" class="font-weight-bold">Emergency Contact</td></tr>
                                <tr><td><strong>Contact Person</strong></td><td>{{ $employee->emergency_contact_name ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Relation</strong></td><td>{{ $employee->emergency_contact_relation ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Emergency Phone</strong></td><td>{{ $employee->emergency_contact_phone ?? 'N/A' }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 2: Employment Details --}}
                    <div class="tab-pane" id="employment">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <tr><td style="width: 25%;"><strong>Designation</strong></td><td>{{ $employee->designation }}</td></tr>
                                <tr><td><strong>Department</strong></td><td>{{ $employee->department ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Date of Joining</strong></td><td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</td></tr>
                                <tr><td><strong>Probation Period</strong></td><td>{{ $employee->probation_period ? $employee->probation_period . ' months' : 'N/A' }}</td></tr>
                                <tr><td><strong>Job Description</strong></td><td>{{ $employee->job_description ?? 'N/A' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Tab 3: Qualification & Experience --}}
                    <div class="tab-pane" id="prof_details">
                        <h5>Qualifications</h5>
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
                        <h5 class="mt-4">Work Experience</h5>
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
                    </div>

                    {{-- Tab 4: Salary & Leaves --}}
                    <div class="tab-pane" id="salary">
                        <h5>Salary Structure</h5>
                        <table class="table table-sm table-hover">
                            <thead class="table-secondary"><tr><th colspan="4">Earnings & Allowances</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td style="width: 20%;"><strong>Basic Salary</strong></td><td style="width: 30%;">{{ number_format($employee->basic_salary ?? 0, 2) }}</td>
                                    <td style="width: 20%;"><strong>House Rent</strong></td><td style="width: 30%;">{{ number_format($employee->house_rent ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Utilities</strong></td><td>{{ number_format($employee->utilities ?? 0, 2) }}</td>
                                    <td><strong>Medical</strong></td><td>{{ number_format($employee->medical ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Conveyance</strong></td><td>{{ number_format($employee->conveyance ?? 0, 2) }}</td>
                                    <td><strong>Other Allowance</strong></td><td>{{ number_format($employee->other_allowance ?? 0, 2) }}</td>
                                </tr>
                                <tr class="table-light"><td class="font-weight-bold">Total Salary (Gross)</td><td class="font-weight-bold" colspan="3">{{ number_format($employee->total_salary, 2) }}</td></tr>
                            </tbody>
                            <thead class="table-secondary"><tr><th colspan="4">Deductions (Future Feature)</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tax, Loans, etc.</strong></td><td>(Details will appear here)</td>
                                    <td></td><td></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light"><tr class="font-weight-bold"><td>Net Payable Salary</td><td colspan="3">{{ number_format($employee->total_salary, 2) }}</td></tr></tfoot>
                        </table>
                        <hr>
                        <h5>Leave Details</h5>
                        <p class="text-muted">Current Period: {{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}</p>
                        <table class="table table-sm table-hover">
                           <tbody>
                                <tr>
                                    <td style="width: 20%;"><strong>Annual</strong></td><td style="width: 30%;">{{ $employee->leaves_annual ?? 0 }}</td>
                                    <td style="width: 20%;"><strong>Sick</strong></td><td style="width: 30%;">{{ $employee->leaves_sick ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Casual</strong></td><td>{{ $employee->leaves_casual ?? 0 }}</td>
                                    <td><strong>Other</strong></td><td>{{ $employee->leaves_other ?? 0 }}</td>
                                </tr>
                                <tr class="table-light"><td class="font-weight-bold">Total Available Leaves</td><td class="font-weight-bold" colspan="3">{{ $employee->total_leaves }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Tab 5: Bank Details --}}
                    <div class="tab-pane" id="bank">
                         <h5>Bank Account Details</h5>
                         <table class="table table-sm table-hover">
                            <tbody>
                                <tr><td style="width: 25%;"><strong>Account Title</strong></td><td>{{ $employee->bank_account_title ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Account Number</strong></td><td>{{ $employee->bank_account_number ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Bank Name</strong></td><td>{{ $employee->bank_name ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Branch Name & Code</strong></td><td>{{ $employee->bank_branch ?? 'N/A' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection