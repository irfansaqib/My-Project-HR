@extends('layouts.admin')

@section('title', 'Employee Profile')

@section('content')
@php
    // ✅ N/A BUG FIX: Use simple string properties directly.
    // The controller doesn't load relations anymore, it saves strings.
    $designationName = $employee->designation ?? 'N/A';
    $departmentName = $employee->department ?? 'N/A';
@endphp

<div class="container-fluid">
    <div class="row">
        {{-- Profile Sidebar --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center mb-3">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/128' }}"
                             alt="User profile picture"
                             style="width: 128px; height: 128px; object-fit: cover;">
                    </div>
                    <h3 class="profile-username text-center">{{ $employee->name }}</h3>
                    <p class="text-muted text-center">{{ $designationName }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Employee #</b>
                            <a class="float-right">{{ $employee->employee_number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b>
                            <a class="float-right">
                                @php
                                    $statusClass = $employee->status === 'active' ? 'success' : 'danger';
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">{{ ucfirst($employee->status) }}</span>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b>Joining Date</b>
                            <a class="float-right">
                                {{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}
                            </a>
                        </li>
                    </ul>

                    @if($employee->status === 'active')
                    <div class="row">
                        <div class="col-6 mb-2">
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-block">
                                <b>Edit Profile</b>
                            </a>
                        </div>
                        <div class="col-6 mb-2">
                            <a href="{{ route('employees.revisions.index', $employee) }}" class="btn btn-info btn-block">
                                <b>Revise Salary</b>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('employees.exit.create', $employee->id) }}" class="btn btn-danger btn-block">
                                <b>Process Exit</b>
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="text-center mt-2">
                        <a href="{{ route('employees.print', $employee) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            Print Details
                        </a>
                        <a href="{{ route('employees.printContract', $employee) }}" target="_blank" class="btn btn-sm btn-outline-info">
                            Print Contract
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Tabs --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    @php
                        $activeWarningsCount = $employee->warnings->where('status', 'active')->count();
                    @endphp
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab">Personal Details</a></li>
                        <li class="nav-item"><a class="nav-link" href="#employment" data-toggle="tab">Employment Details</a></li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeWarningsCount >= 3 ? 'bg-danger' : '' }}" href="#warnings" data-toggle="tab">
                                Warnings <span class="badge {{ $activeWarningsCount >= 3 ? 'badge-light' : 'badge-warning' }} ml-1">{{ $activeWarningsCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#qualifications" data-toggle="tab">Qualification & Experience</a></li>
                        <li class="nav-item"><a class="nav-link" href="#salary" data-toggle="tab">Salary Structure</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('employees.incentives.index', $employee) }}">Bonuses</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('employees.revisions.index', $employee) }}">Salary History</a></li>
                        <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank Details</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">

                        {{-- PERSONAL TAB --}}
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
                                <dd class="col-sm-8">{{ $employee->email ?? 'N/A' }}</dd>
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

                        {{-- EMPLOYMENT TAB --}}
                        <div class="tab-pane" id="employment">
                            <strong><i class="fas fa-briefcase mr-1"></i> Employment Details</strong>
                            <dl class="row mt-2">
                                <dt class="col-sm-4">Designation</dt>
                                <dd class="col-sm-8">{{ $designationName }}</dd>
                                <dt class="col-sm-4">Department</dt>
                                <dd class="col-sm-8">{{ $departmentName }}</dd>
                                <dt class="col-sm-4">Joining Date</dt>
                                <dd class="col-sm-8">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</dd>
                                <dt class="col-sm-4">Job Description</dt>
                                <dd class="col-sm-8">{!! nl2br(e($employee->job_description ?? 'N/A')) !!}</dd>
                            </dl>

                            @if($employee->exit_date)
                                <hr>
                                <strong><i class="fas fa-sign-out-alt text-danger mr-1"></i> Exit Details</strong>
                                <dl class="row mt-2">
                                    <dt class="col-sm-4">Exit Date</dt>
                                    <dd class="col-sm-8">{{ \Carbon\Carbon::parse($employee->exit_date)->format('d M, Y') }}</dd>
                                    <dt class="col-sm-4">Type of Exit</dt>
                                    <dd class="col-sm-8">{{ ucfirst($employee->exit_type) }}</dd>
                                    <dt class="col-sm-4">Reason</dt>
                                    <dd class="col-sm-8">{{ $employee->exit_reason }}</dd>
                                </dl>
                            @endif

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
                                            <td colspan="2" class="text-center text-muted">No leave types assigned.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- SALARY STRUCTURE TAB --}}
                        <div class="tab-pane" id="salary">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong><i class="fas fa-money-bill-wave mr-1"></i> Current Salary Package</strong>
                            </div>

                            <table class="table table-sm mt-2" style="max-width: 600px;">
                                <tr>
                                    <th>Basic Salary</th>
                                    <td class="text-right">{{ number_format($employee->basic_salary, 2) }}</td>
                                </tr>

                                {{-- Allowances --}}
                                @foreach($allowances as $entry)
                                    <tr>
                                        <th>{{ $entry['model']->name }}</th>
                                        <td class="text-right">{{ number_format($entry['amount'], 2) }}</td>
                                    </tr>
                                @endforeach

                                <tr class="bg-light font-weight-bold">
                                    <th>Gross Salary</th>
                                    <td class="text-right">{{ number_format($computedGross, 2) }}</td>
                                </tr>

                                {{-- Deductions (Includes Tax now) --}}
                                @forelse($deductions as $entry)
                                    <tr>
                                        <th>{{ $entry['model']->name }}</th>
                                        <td class="text-right text-danger">({{ number_format($entry['amount'], 2) }})</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No deductions found.</td>
                                    </tr>
                                @endforelse

                                {{-- ✅ FIXED: Removed the hardcoded "Income Tax (Estimated)" block --}}

                                <tr class="bg-secondary text-white font-weight-bold">
                                    <th>Net Salary</th>
                                    <td class="text-right">{{ number_format($computedNet, 2) }}</td>
                                </tr>
                            </table>

                            <p class="text-muted small">
                                <i class="fas fa-info-circle"></i>
                                Displayed values are based on the latest approved salary revision.
                            </p>
                        </div>

                        {{-- BANK DETAILS --}}
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

                        {{-- WARNINGS TAB --}}
                        <div class="tab-pane" id="warnings">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong><i class="fas fa-exclamation-circle mr-1"></i> Disciplinary Warnings</strong>
                                @if($employee->status === 'active')
                                <a href="{{ route('warnings.create', ['employee' => $employee->id]) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-plus-circle"></i> Issue Warning
                                </a>
                                @endif
                            </div>

                            @forelse($employee->warnings as $warning)
                                <div class="post {{ $warning->status == 'withdrawn' ? 'text-muted' : '' }}">
                                    <div class="user-block">
                                        <span class="username">
                                            <a href="#">{{ $warning->subject }}</a>
                                            @if($warning->status == 'withdrawn')
                                                <span class="badge badge-secondary ml-2">Withdrawn</span>
                                            @endif
                                        </span>
                                        <span class="description">
                                            Issued on {{ $warning->warning_date->format('d M, Y') }} by {{ $warning->issuer->name }}
                                        </span>
                                    </div>
                                    <p>{{ $warning->description }}</p>
                                    @if($warning->action_taken)
                                        <p><strong>Action/Recommendation:</strong> {{ $warning->action_taken }}</p>
                                    @endif
                                    @if($warning->status == 'active')
                                        <form action="{{ route('warnings.destroy', $warning->id) }}" method="POST" onsubmit="return confirm('Withdraw this warning?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Withdraw</button>
                                        </form>
                                    @endif
                                </div>
                                @if(!$loop->last) <hr> @endif
                            @empty
                                <div class="alert alert-info">No disciplinary warnings issued.</div>
                            @endforelse
                        </div>

                        {{-- QUALIFICATIONS TAB --}}
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
                                            <td>
                                                {{ \Carbon\Carbon::parse($exp->from_date)->format('M Y') }}
                                                to
                                                {{ \Carbon\Carbon::parse($exp->to_date)->format('M Y') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">No work experience listed.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection