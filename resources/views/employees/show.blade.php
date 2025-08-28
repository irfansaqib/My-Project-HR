@extends('layouts.admin')

@section('title', 'View Employee')

@section('content')
<div class="row">
    <div class="col-md-3">

        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/128' }}"
                         alt="User profile picture" style="width: 128px; height: 128px; object-fit: cover;">
                </div>

                <h3 class="profile-username text-center">{{ $employee->name }}</h3>
                <p class="text-muted text-center">{{ $employee->designation }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Employee ID</b> <a class="float-right">{{ $employee->employee_number }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right"><span class="badge {{ $employee->status == 'active' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($employee->status) }}</span></a>
                    </li>
                </ul>

                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-block"><b>Edit Profile</b></a>
                <a href="{{ route('employees.print', $employee) }}" class="btn btn-secondary btn-block" target="_blank"><b>Print Form</b></a>
            </div>
        </div>

    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab"><i class="fas fa-user-circle mr-1"></i> Personal</a></li>
                    <li class="nav-item"><a class="nav-link" href="#employment" data-toggle="tab"><i class="fas fa-briefcase mr-1"></i> Employment</a></li>
                    <li class="nav-item"><a class="nav-link" href="#compensation" data-toggle="tab"><i class="fas fa-money-bill-wave mr-1"></i> Salary & Leaves</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <div class="active tab-pane" id="personal">
                        <h5><i class="fas fa-id-card text-info mr-2"></i> Basic Information</h5>
                        <table class="table table-sm table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Father's Name</strong></td>
                                    <td>{{ $employee->father_name ?? 'N/A' }}</td>
                                    <td style="width: 25%;"><strong>CNIC</strong></td>
                                    <td>{{ $employee->cnic }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Birth</strong></td>
                                    <td>{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M, Y') : 'N/A' }}</td>
                                    <td><strong>Gender</strong></td>
                                    <td>{{ $employee->gender ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 class="mt-4"><i class="fas fa-phone-alt text-success mr-2"></i> Contact Details</h5>
                        <table class="table table-sm table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Phone Number</strong></td>
                                    <td>{{ $employee->phone }}</td>
                                    <td style="width: 25%;"><strong>Email Address</strong></td>
                                    <td>{{ $employee->email }}</td>
                                </tr>
                                 <tr>
                                    <td><strong>Address</strong></td>
                                    <td colspan="3">{{ $employee->address ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 class="mt-4"><i class="fas fa-ambulance text-danger mr-2"></i> Emergency Contact</h5>
                         <table class="table table-sm table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Contact Person</strong></td>
                                    <td>{{ $employee->emergency_contact_name ?? 'N/A' }}</td>
                                    <td style="width: 25%;"><strong>Relation</strong></td>
                                    <td>{{ $employee->emergency_contact_relation ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone Number</strong></td>
                                    <td colspan="3">{{ $employee->emergency_contact_phone ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane" id="employment">
                        <h5><i class="fas fa-info-circle text-primary mr-2"></i> Job Information</h5>
                        <table class="table table-sm table-borderless table-hover">
                             <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Department</strong></td>
                                    <td>{{ $employee->department ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Joining</strong></td>
                                    <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 class="mt-4"><i class="fas fa-graduation-cap text-warning mr-2"></i> Qualifications</h5>
                        <table class="table table-hover table-sm">
                            <thead><tr><th>Degree / Title</th><th>Institute</th><th>Year of Passing</th></tr></thead>
                            <tbody>
                                @forelse($employee->qualifications as $qual)
                                <tr><td>{{ $qual->degree_title }}</td><td>{{ $qual->institute }}</td><td>{{ $qual->year_of_passing }}</td></tr>
                                @empty
                                <tr><td colspan="3" class="text-center">No qualifications listed.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <h5 class="mt-4"><i class="fas fa-history text-secondary mr-2"></i> Work Experience</h5>
                         <table class="table table-hover table-sm">
                            <thead><tr><th>Company</th><th>Job Title</th><th>Period</th></tr></thead>
                            <tbody>
                                @forelse($employee->experiences as $exp)
                                <tr><td>{{ $exp->company_name }}</td><td>{{ $exp->job_title }}</td><td>{{ \Carbon\Carbon::parse($exp->from_date)->format('M Y') }} - {{ \Carbon\Carbon::parse($exp->to_date)->format('M Y') }}</td></tr>
                                @empty
                                <tr><td colspan="3" class="text-center">No experience listed.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane" id="compensation">
                        <h5><i class="fas fa-wallet text-purple mr-2"></i> Salary Details</h5>
                        <table class="table table-sm table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Basic Salary</strong></td>
                                    <td>{{ number_format($employee->basic_salary, 2) }}</td>
                                    <td style="width: 25%;"><strong>House Rent</strong></td>
                                    <td>{{ number_format($employee->house_rent, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Utilities</strong></td>
                                    <td>{{ number_format($employee->utilities, 2) }}</td>
                                    <td><strong>Medical</strong></td>
                                    <td>{{ number_format($employee->medical, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Conveyance</strong></td>
                                    <td>{{ number_format($employee->conveyance, 2) }}</td>
                                    <td><strong>Other Allowance</strong></td>
                                    <td>{{ number_format($employee->other_allowance, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Salary</strong></td>
                                    <td><strong>{{ number_format($employee->total_salary, 2) }}</strong></td>
                                    <td></td><td></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <h5 class="mt-4"><i class="fas fa-calendar-alt text-teal mr-2"></i> Leave Details</h5>
                        <table class="table table-sm table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <td style="width: 25%;"><strong>Leave Period</strong></td>
                                    <td colspan="3">{{ $employee->leave_period_from ? \Carbon\Carbon::parse($employee->leave_period_from)->format('d M, Y') : 'N/A' }} to {{ $employee->leave_period_to ? \Carbon\Carbon::parse($employee->leave_period_to)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Annual Leaves</strong></td>
                                    <td>{{ $employee->leaves_annual }}</td>
                                    <td><strong>Sick Leaves</strong></td>
                                    <td>{{ $employee->leaves_sick }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Casual Leaves</strong></td>
                                    <td>{{ $employee->leaves_casual }}</td>
                                    <td><strong>Other Leaves</strong></td>
                                    <td>{{ $employee->leaves_other }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Leaves</strong></td>
                                    <td><strong>{{ $employee->total_leaves }}</strong></td>
                                    <td></td><td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection