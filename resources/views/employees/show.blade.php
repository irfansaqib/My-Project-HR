@extends('layouts.admin')

@section('title', 'Employee Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/128' }}"
                             alt="User profile picture"
                             style="width: 128px; height: 128px; object-fit: cover;">
                    </div>
                    <h3 class="profile-username text-center">{{ $employee->name }}</h3>
                    <p class="text-muted text-center">{{ $employee->designation }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Employee #</b> <a class="float-right">{{ $employee->employee_number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> 
                            <a class="float-right">
                                <span class="badge badge-{{ $employee->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($employee->status) }}</span>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b>Joining Date</b> <a class="float-right">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</a>
                        </li>
                    </ul>

                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-block mb-2"><b>Edit Profile</b></a>
                     <div class="text-center">
                        <a href="{{ route('employees.print', $employee) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Print Details</a>
                        <a href="{{ route('employees.printContract', $employee) }}" target="_blank" class="btn btn-sm btn-outline-info">Print Contract</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab">Personal Details</a></li>
                        <li class="nav-item"><a class="nav-link" href="#employment" data-toggle="tab">Employment Details</a></li>
                        <li class="nav-item"><a class="nav-link" href="#qualifications" data-toggle="tab">Qualification & Experience</a></li>
                        <li class="nav-item"><a class="nav-link" href="#salary" data-toggle="tab">Salary Structure</a></li>
                        <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank Details</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="personal">
                            <strong><i class="fas fa-user mr-1"></i> Personal Information</strong>
                            <dl class="row mt-2">
                                <dt class="col-sm-4">Father's Name</dt>
                                <dd class="col-sm-8">{{ $employee->father_name ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">CNIC</dt>
                                <dd class="col-sm-8">{{ $employee->cnic }}</dd>
                                <dt class="col-sm-4">Date of Birth</dt>
                                <dd class="col-sm-8">{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M, Y') : 'N/A' }}</dd>
                                <dt class="col-sm-4">Gender</dt>
                                <dd class="col-sm-8">{{ $employee->gender ?? 'N/A' }}</dd>
                            </dl>
                            <hr>
                            <strong><i class="fas fa-phone mr-1"></i> Contact Details</strong>
                            <dl class="row mt-2">
                                <dt class="col-sm-4">Phone Number</dt>
                                <dd class="col-sm-8">{{ $employee->phone }}</dd>
                                <dt class="col-sm-4">Email Address</dt>
                                <dd class="col-sm-8">{{ $employee->email }}</dd>
                                <dt class="col-sm-4">Address</dt>
                                <dd class="col-sm-8">{{ $employee->address ?? 'N/A' }}</dd>
                            </dl>
                             <hr>
                            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Emergency Contact</strong>
                            <dl class="row mt-2">
                                <dt class="col-sm-4">Contact Person</dt>
                                <dd class="col-sm-8">{{ $employee->emergency_contact_name ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Relation</dt>
                                <dd class="col-sm-8">{{ $employee->emergency_contact_relation ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Phone Number</dt>
                                <dd class="col-sm-8">{{ $employee->emergency_contact_phone ?? 'N/A' }}</dd>
                            </dl>
                        </div>

                        <div class="tab-pane" id="employment">
                             <strong><i class="fas fa-building mr-1"></i> Employment Details</strong>
                             <dl class="row mt-2">
                                <dt class="col-sm-4">Designation</dt>
                                <dd class="col-sm-8">{{ $employee->designation }}</dd>
                                <dt class="col-sm-4">Department</dt>
                                <dd class="col-sm-8">{{ $employee->department ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Joining Date</dt>
                                <dd class="col-sm-8">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</dd>
                                <dt class="col-sm-4">Probation Period</dt>
                                <dd class="col-sm-8">{{ $employee->probation_period ? $employee->probation_period . ' Months' : 'N/A' }}</dd>
                                <dt class="col-sm-4">Job Description</dt>
                                <dd class="col-sm-8">{{ $employee->job_description ?? 'N/A' }}</dd>
                            </dl>
                            <hr>
                            <strong><i class="fas fa-calendar-check mr-1"></i> Leave Allotment</strong>
                            <table class="table table-sm table-bordered mt-2" style="max-width: 600px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Leave Type</th>
                                        <th class="text-right">Days Allotted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->leaveTypes as $leaveType)
                                        <tr>
                                            <td>{{ $leaveType->name }}</td>
                                            <td class="text-right">{{ $leaveType->pivot->days_allotted }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No leave types have been assigned.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>Total Leaves</td>
                                        {{-- ** THIS IS THE CORRECTED LINE ** --}}
                                        <td class="text-right">{{ $employee->leaveTypes->sum('pivot.days_allotted') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="tab-pane" id="qualifications">
                             <strong><i class="fas fa-graduation-cap mr-1"></i> Qualifications</strong>
                             <table class="table table-bordered table-striped mt-2">
                                 <thead>
                                     <tr>
                                         <th>Degree</th>
                                         <th>Institute</th>
                                         <th>Year</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     @forelse($employee->qualifications as $q)
                                     <tr>
                                         <td>{{ $q->degree_title }}</td>
                                         <td>{{ $q->institute }}</td>
                                         <td>{{ $q->year_of_passing }}</td>
                                     </tr>
                                     @empty
                                     <tr><td colspan="3" class="text-muted text-center">No qualifications listed.</td></tr>
                                     @endforelse
                                 </tbody>
                             </table>
                            <hr>
                            <strong><i class="fas fa-briefcase mr-1"></i> Work Experience</strong>
                            <table class="table table-bordered table-striped mt-2">
                                 <thead>
                                     <tr>
                                         <th>Company</th>
                                         <th>Job Title</th>
                                         <th>Duration</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     @forelse($employee->experiences as $exp)
                                     <tr>
                                         <td>{{ $exp->company_name }}</td>
                                         <td>{{ $exp->job_title }}</td>
                                         <td>{{ \Carbon\Carbon::parse($exp->from_date)->format('M Y') }} to {{ \Carbon\Carbon::parse($exp->to_date)->format('M Y') }}</td>
                                     </tr>
                                     @empty
                                     <tr><td colspan="3" class="text-muted text-center">No work experience listed.</td></tr>
                                     @endforelse
                                 </tbody>
                             </table>
                        </div>
                        
                        <div class="tab-pane" id="salary">
                             <strong><i class="fas fa-money-bill-wave mr-1"></i> Salary Package</strong>
                             <table class="table table-sm mt-2" style="max-width: 600px;">
                                <tr>
                                    <th>Basic Salary</th>
                                    <td class="text-right">{{ number_format($employee->basic_salary, 2) }}</td>
                                </tr>
                                @foreach($employee->salaryComponents->where('type', 'allowance') as $component)
                                    <tr><th>{{ $component->name }}</th><td class="text-right">{{ number_format($component->pivot->amount, 2) }}</td></tr>
                                @endforeach
                                <tr class="bg-light font-weight-bold"><th>Gross Salary</th><td class="text-right">{{ number_format($employee->gross_salary, 2) }}</td></tr>
                                
                                @foreach($employee->salaryComponents->where('type', 'deduction') as $component)
                                    <tr><th>{{ $component->name }}</th><td class="text-right text-danger">({{ number_format($component->pivot->amount, 2) }})</td></tr>
                                @endforeach
                                
                                <tr>
                                    <th>Income Tax</th>
                                    <td class="text-right text-danger">({{ number_format($monthlyTax, 2) }})</td>
                                </tr>

                                <tr class="bg-secondary font-weight-bold">
                                    <th>Net Salary</th>
                                    <td class="text-right">{{ number_format($employee->net_salary, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="tab-pane" id="bank">
                            <strong><i class="fas fa-university mr-1"></i> Bank Account Details</strong>
                            <dl class="row mt-2">
                                <dt class="col-sm-4">Account Title</dt>
                                <dd class="col-sm-8">{{ $employee->bank_account_title ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Account Number</dt>
                                <dd class="col-sm-8">{{ $employee->bank_account_number ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Bank Name</dt>
                                <dd class="col-sm-8">{{ $employee->bank_name ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Branch</dt>
                                <dd class="col-sm-8">{{ $employee->bank_branch ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection