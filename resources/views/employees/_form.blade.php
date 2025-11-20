{{-- resources/views/employees/_form.blade.php --}}
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/4.0.13/select2-bootstrap4.min.css" />
<style>
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #ced4da;
        height: calc(2.25rem + 2px) !important;
    }
    .input-group > .select2-container--bootstrap4 .select2-selection {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .input-group > .select2-container--bootstrap4 { width: 1% !important; flex: 1 1 auto; }

    .form-section-header {
        background-color: #eef2f7;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
    }
    .form-section-header h5 { margin: 0; font-size: 1.1rem; font-weight: 600; color: #495057; }
    .card-body > .form-section-header:first-of-type { margin-top: 0; }

    .bg-subtle { background:#f8f9fb; }
</style>
@endpush

@php
    $isEditMode = isset($employee) && $employee->id;
@endphp

{{-- Inline Modals --}}
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Designation</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="new_designation_name">Designation Name</label>
            <input type="text" class="form-control" id="new_designation_name">
            <div id="designation-error" class="text-danger mt-2 d-none"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="saveDesignationBtn" class="btn btn-primary">Save Designation</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Department</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="new_department_name">Department Name</label>
            <input type="text" class="form-control" id="new_department_name">
            <div id="department-error" class="text-danger mt-2 d-none"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="saveDepartmentBtn" class="btn btn-primary">Save Department</button>
      </div>
    </div>
  </div>
</div>

<div class="card-body">
    {{-- Personal Information --}}
    <div class="form-section-header"><h5>Personal Information</h5></div>

    <div class="row">
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $employee->name ?? '') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="father_name">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" id="father_name" value="{{ old('father_name', $employee->father_name ?? '') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="cnic">CNIC <span class="text-danger">*</span></label>
                    <input type="text" name="cnic" class="form-control" id="cnic" value="{{ old('cnic', $employee->cnic ?? '') }}" required placeholder="00000-0000000-0">
                </div>
                <div class="col-md-6 form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob', $employee->dob ?? '') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" @if(old('gender', $employee->gender ?? '') == 'Male') selected @endif>Male</option>
                        <option value="Female" @if(old('gender', $employee->gender ?? '') == 'Female') selected @endif>Female</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-3 text-center">
            <label>Employee Photo</label>
            <div class="mb-2">
                <img id="photo-preview" src="{{ isset($employee) && $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/150' }}" class="img-thumbnail" style="width:150px;height:150px;object-fit:cover;">
            </div>
            <div class="custom-file">
                <input type="file" name="photo" class="custom-file-input" id="photo" accept="image/*">
                <label class="custom-file-label" for="photo">Choose photo</label>
            </div>
        </div>
    </div>

    {{-- Contact Details --}}
    <div class="form-section-header"><h5>Contact Details</h5></div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="phone">Contact Number <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" id="phone" value="{{ old('phone', $employee->phone ?? '') }}" required>
        </div>
        <div class="col-md-6 form-group">
            <label for="email">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $employee->email ?? '') }}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="address">Address</label>
        <textarea name="address" class="form-control" id="address" rows="3">{{ old('address', $employee->address ?? '') }}</textarea>
    </div>

    {{-- Emergency Contact --}}
    <div class="form-section-header"><h5>Emergency Contact Details</h5></div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label for="emergency_contact_name">Contact Person Name</label>
            <input type="text" name="emergency_contact_name" class="form-control" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
            <label for="emergency_contact_relation">Relation</label>
            <input type="text" name="emergency_contact_relation" class="form-control" id="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
            <label for="emergency_contact_phone">Contact Number</label>
            <input type="text" name="emergency_contact_phone" class="form-control" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}">
        </div>
    </div>

    {{-- Employment Details --}}
    <div class="form-section-header"><h5>Employment Details</h5></div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label>Employee ID</label>
            <input type="text" class="form-control" value="{{ $employee->employee_number ?? '(Auto Generated)' }}" readonly disabled style="background-color: #e9ecef; font-weight: bold;">
        </div>

        <div class="col-md-4 form-group">
            <label for="designation">Designation <span class="text-danger">*</span></label>
            <div class="input-group">
                <select name="designation" id="designation" class="form-control" required>
                    <option value="">Select a Designation</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->name }}" @if(old('designation', $employee->designation ?? '') == $designation->name) selected @endif>{{ $designation->name }}</option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDesignationModal">+</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 form-group">
            <label for="department">Department</label>
            <div class="input-group">
                <select name="department" id="department" class="form-control">
                    <option value="">Select a Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->name }}" @if(old('department', $employee->department ?? '') == $department->name) selected @endif>{{ $department->name }}</option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDepartmentModal">+</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="joining_date">Date of Joining</label>
            <input type="date" name="joining_date" class="form-control" id="joining_date" value="{{ old('joining_date', $employee->joining_date ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="active" @if(old('status', $employee->status ?? 'active') == 'active') selected @endif>Active</option>
                <option value="resigned" @if(old('status', $employee->status ?? '') == 'resigned') selected @endif>Resigned</option>
                <option value="terminated" @if(old('status', $employee->status ?? '') == 'terminated') selected @endif>Terminated</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label for="probation_period">Probation Period (Months)</label>
            <input type="number" name="probation_period" class="form-control" id="probation_period" value="{{ old('probation_period', $employee->probation_period ?? 3) }}">
        </div>
    </div>

    <div class="form-group">
        <label for="job_description">Job Description</label>
        <textarea name="job_description" class="form-control" id="job_description" rows="4">{{ old('job_description', $employee->job_description ?? '') }}</textarea>
    </div>

    {{-- Qualifications --}}
    <div class="form-section-header d-flex justify-content-between align-items-center">
        <h5>Qualifications</h5>
        <button type="button" class="btn btn-primary btn-sm" id="add-qualification">+</button>
    </div>
    <div id="qualifications-wrapper">
        @if(isset($employee) && $employee->qualifications)
            @foreach ($employee->qualifications as $index => $qualification)
                <div class="row mb-2">
                    <input type="hidden" name="qualifications[{{ $index }}][id]" value="{{ $qualification->id }}">
                    <div class="col-md-4"><input type="text" name="qualifications[{{ $index }}][degree_title]" class="form-control" placeholder="Degree Title" value="{{ $qualification->degree_title }}"></div>
                    <div class="col-md-4"><input type="text" name="qualifications[{{ $index }}][institute]" class="form-control" placeholder="Institute" value="{{ $qualification->institute }}"></div>
                    <div class="col-md-3"><input type="number" name="qualifications[{{ $index }}][year_of_passing]" class="form-control" placeholder="Year" value="{{ $qualification->year_of_passing }}"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Work Experience --}}
    <div class="form-section-header d-flex justify-content-between align-items-center">
        <h5>Work Experience</h5>
        <button type="button" class="btn btn-primary btn-sm" id="add-experience">+</button>
    </div>
    <div id="experiences-wrapper">
        @if(isset($employee) && $employee->experiences)
            @foreach ($employee->experiences as $index => $experience)
                <div class="row mb-2">
                    <input type="hidden" name="experiences[{{ $index }}][id]" value="{{ $experience->id }}">
                    <div class="col-md-3"><input type="text" name="experiences[{{ $index }}][company_name]" class="form-control" placeholder="Company" value="{{ $experience->company_name }}"></div>
                    <div class="col-md-3"><input type="text" name="experiences[{{ $index }}][job_title]" class="form-control" placeholder="Job Title" value="{{ $experience->job_title }}"></div>
                    <div class="col-md-2"><input type="date" name="experiences[{{ $index }}][from_date]" class="form-control" value="{{ $experience->from_date }}"></div>
                    <div class="col-md-2"><input type="date" name="experiences[{{ $index }}][to_date]" class="form-control" value="{{ $experience->to_date }}"></div>
                    <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Salary Package --}}
    <div class="form-section-header"><h5>Salary Package</h5></div>

    @if($isEditMode)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            To change the salary, please use the <strong>Salary Revision</strong> module.
            <a href="{{ route('employees.revisions.index', $employee) }}" class="btn btn-primary btn-sm float-right" style="margin-top: -5px;">Go to Salary Revisions</a>
        </div>
    @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>
            Set the <strong>Initial Salary Package</strong> below. This will automatically create an approved salary structure.
        </div>
    @endif

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="basic_salary">Basic Salary <span class="text-danger">*</span></label>
            <input type="text" name="basic_salary" class="form-control salary-calc" id="basic_salary" 
                   value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}" 
                   {{ $isEditMode ? 'readonly' : '' }}>
        </div>
    </div>

    {{-- Allowances --}}
    <div class="card card-outline card-success">
        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-plus-circle"></i> Allowances</h3></div>
        <div class="card-body">
            <div class="row">
                @forelse ($allowances as $allowance)
                    <div class="col-md-4 form-group">
                        <label for="component_{{ $allowance->id }}">{{ $allowance->name }}</label>
                        <input type="text"
                               name="components[{{ $allowance->id }}]"
                               class="form-control salary-calc allowance"
                               id="component_{{ $allowance->id }}"
                               value="{{ old('components.'.$allowance->id, $componentAmounts[$allowance->id] ?? 0) }}"
                               {{ $isEditMode ? 'readonly' : '' }}>
                    </div>
                @empty
                    <div class="col-12"><p class="text-muted mb-0">No allowance components defined.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Deductions --}}
    <div class="card card-outline card-danger mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-minus-circle"></i> Deductions</h3>
            
            {{-- ✅ TAX CALCULATOR: Visible ONLY on Create Form --}}
            @if(!$isEditMode)
                <button type="button" class="btn btn-outline-info btn-sm" id="btn-open-tax-calc">
                    <i class="fas fa-calculator mr-1"></i> Open Tax Calculator
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                @forelse ($deductions as $deduction)
                    <div class="col-md-4 form-group">
                        <label for="component_{{ $deduction->id }}">{{ $deduction->name }}</label>
                        <input type="text"
                               name="components[{{ $deduction->id }}]"
                               class="form-control salary-calc deduction"
                               id="component_{{ $deduction->id }}"
                               value="{{ old('components.'.$deduction->id, $componentAmounts[$deduction->id] ?? 0) }}"
                               {{ $isEditMode ? 'readonly' : '' }}>
                    </div>
                @empty
                    <div class="col-12"><p class="text-muted mb-0">No deduction components defined.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Totals --}}
    <div class="row mt-3 bg-subtle pt-3 rounded">
        <div class="col-md-6 form-group">
            <label for="gross_salary">Gross Salary</label>
            <input type="text" name="gross_salary" class="form-control" id="gross_salary" value="{{ old('gross_salary', $employee->gross_salary ?? '0.00') }}" readonly style="font-weight:bold;background-color:#e9ecef;">
        </div>
        <div class="col-md-6 form-group">
            <label for="net_salary">Net Salary</label>
            <input type="text" name="net_salary" class="form-control" id="net_salary" value="{{ old('net_salary', $employee->net_salary ?? '0.00') }}" readonly style="font-weight:bold;background-color:#e9ecef;">
        </div>
    </div>

    {{-- Bank Account --}}
    <div class="form-section-header"><h5>Bank Account Details</h5></div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="bank_account_title">Account Title</label>
            <input type="text" name="bank_account_title" class="form-control" id="bank_account_title" value="{{ old('bank_account_title', $employee->bank_account_title ?? '') }}">
        </div>
        <div class="col-md-6 form-group">
            <label for="bank_account_number">Account Number</label>
            <input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="bank_name">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" id="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}">
        </div>
        <div class="col-md-6 form-group">
            <label for="bank_branch">Branch Name & Code</label>
            <input type="text" name="bank_branch" class="form-control" id="bank_branch" value="{{ old('bank_branch', $employee->bank_branch ?? '') }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 form-group">
            <label for="business_bank_account_id">Paying Bank Account (from Business)</label>
            <select name="business_bank_account_id" id="business_bank_account_id" class="form-control">
                <option value="">-- Select a Bank Account --</option>
                @foreach($businessBankAccounts as $account)
                    <option value="{{ $account->id }}" @if(old('business_bank_account_id', $employee->business_bank_account_id ?? '') == $account->id) selected @endif>{{ $account->bank_name }} - ({{ $account->account_number }})</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Leaves --}}
    <div class="form-section-header"><h5>Leaves Details</h5></div>
    <div class="row">
        <div class="col-md-6 form-group"><label for="leave_period_from">Leave Period From</label><input type="date" name="leave_period_from" class="form-control" id="leave_period_from" value="{{ old('leave_period_from', $employee->leave_period_from ?? '') }}"></div>
        <div class="col-md-6 form-group"><label for="leave_period_to">Leave Period To</label><input type="date" name="leave_period_to" class="form-control" id="leave_period_to" value="{{ old('leave_period_to', $employee->leave_period_to ?? '') }}"></div>
    </div>

    <div class="row">
        @forelse ($leaveTypes as $leaveType)
            @php
                $assignedLeave = isset($employee) ? $employee->leaveTypes->firstWhere('id', $leaveType->id) : null;
                $daysAllotted = $assignedLeave ? $assignedLeave->pivot->days_allotted : 0;
            @endphp
            <div class="col-md-3 form-group">
                <label for="leave_{{ $leaveType->id }}">{{ $leaveType->name }} (Days)</label>
                <input type="number" name="leaves[{{ $leaveType->id }}]" id="leave_{{ $leaveType->id }}" class="form-control leave-calc" value="{{ old('leaves.'.$leaveType->id, $daysAllotted) }}">
            </div>
        @empty
            <div class="col-12"><p class="text-muted mb-0">No leave types defined.</p></div>
        @endforelse
    </div>

    <div class="row bg-subtle pt-3 rounded mt-2">
        <div class="col-md-3 form-group">
            <label for="total_leaves">Total Leaves</label>
            <input type="text" class="form-control" id="total_leaves" readonly style="font-weight:bold;background-color:#e9ecef;">
        </div>
    </div>

    {{-- Documents --}}
    <div class="form-section-header"><h5>Other Documents</h5></div>
    <div class="form-group">
        <label for="attachment">Attach Document (PDF, JPG, PNG)</label>
        <div class="custom-file">
            <input type="file" name="attachment" class="custom-file-input" id="attachment">
            <label class="custom-file-label" for="attachment">Choose file</label>
        </div>
        @if(isset($employee) && $employee->attachment_path)
            <div class="mt-2">Current file: <a href="{{ asset('storage/' . $employee->attachment_path) }}" target="_blank">View Document</a></div>
        @endif
    </div>
    
    {{-- Login Details (Create Only) --}}
    @if(!$isEditMode)
        <div class="form-section-header"><h5>Login Account Details</h5></div>
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="create_user_account" name="create_user_account" value="1" {{ old('create_user_account') ? 'checked' : '' }}>
                <label class="custom-control-label" for="create_user_account">Create User Account for Employee Portal Access</label>
            </div>
        </div>
        <div class="row" id="user-account-fields" style="display: none;">
            <div class="col-md-6 form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <div class="col-md-6 form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
$(function(){
    $('#designation, #department').select2({ theme: 'bootstrap4' });
    $('#cnic').mask('00000-0000000-0');

    function formatNumber(num) { return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(num || 0)); }
    function unformatNumber(str) { return parseFloat(String(str).replace(/,/g, '').replace(/[()]/g, '')) || 0; }

    function calculateSalary() {
        let basic = unformatNumber($('#basic_salary').val());
        let totalAllowances = 0, totalDeductions = 0;
        $('.allowance').each(function(){ totalAllowances += unformatNumber($(this).val()); });
        $('.deduction').each(function(){ totalDeductions += unformatNumber($(this).val()); });
        let gross = basic + totalAllowances;
        let net = gross - totalDeductions;
        $('#gross_salary').val(formatNumber(gross));
        $('#net_salary').val(formatNumber(net));
    }

    $(document).on('blur', '.salary-calc', function(){ $(this).val(formatNumber(unformatNumber($(this).val()))); });
    $(document).on('input', '.salary-calc', calculateSalary);
    calculateSalary();

    function calculateLeaves() {
        let total = 0;
        $('.leave-calc').each(function(){ total += parseInt($(this).val()) || 0; });
        $('#total_leaves').val(total);
    }
    $('.leave-calc').on('input', calculateLeaves);
    calculateLeaves();

    $('.custom-file-input').on('change', function(e){
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass('selected').html(fileName);
        if(this.id === 'photo' && e.target.files[0]){
            var reader = new FileReader();
            reader.onload = e => $('#photo-preview').attr('src', e.target.result);
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // AJAX save designation/department
    function saveNewItem(name, url, selectId, modalSelector, errorSelector, inputSelector) {
        const headers = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
        $.ajax({
            url, type: 'POST', headers, data: { name },
            success: function(res){
                const newItem = res.designation || res.department || res;
                const opt = new Option(newItem.name, newItem.name, true, true);
                $('#' + selectId).append(opt).trigger('change');
                $(modalSelector).modal('hide');
                $(inputSelector).val('');
                $(errorSelector).addClass('d-none').text('');
            },
            error: function(xhr){
                const msg = xhr.responseJSON?.errors?.name?.[0] || 'Error saving item.';
                $(errorSelector).removeClass('d-none').text(msg);
            }
        });
    }
    $('#saveDesignationBtn').on('click', function(){
        saveNewItem($('#new_designation_name').val(), "{{ route('designations.store') }}", 'designation', '#addDesignationModal', '#designation-error', '#new_designation_name');
    });
    $('#saveDepartmentBtn').on('click', function(){
        saveNewItem($('#new_department_name').val(), "{{ route('departments.store') }}", 'department', '#addDepartmentModal', '#department-error', '#new_department_name');
    });

    // Dynamic rows (Qualifications)
    let qualIndex = {{ isset($employee) && $employee->qualifications ? $employee->qualifications->count() : 0 }};
    $('#add-qualification').on('click', function(){
        $('#qualifications-wrapper').append(`<div class="row mb-2">
            <div class="col-md-4"><input type="text" name="qualifications[new_${qualIndex}][degree_title]" class="form-control" placeholder="Degree Title"></div>
            <div class="col-md-4"><input type="text" name="qualifications[new_${qualIndex}][institute]" class="form-control" placeholder="Institute"></div>
            <div class="col-md-3"><input type="number" name="qualifications[new_${qualIndex}][year_of_passing]" class="form-control" placeholder="Year"></div>
            <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
        </div>`);
        qualIndex++;
    });

    // Dynamic rows (Experience)
    let expIndex = {{ isset($employee) && $employee->experiences ? $employee->experiences->count() : 0 }};
    $('#add-experience').on('click', function(){
        $('#experiences-wrapper').append(`<div class="row mb-2">
            <div class="col-md-3"><input type="text" name="experiences[new_${expIndex}][company_name]" class="form-control" placeholder="Company"></div>
            <div class="col-md-3"><input type="text" name="experiences[new_${expIndex}][job_title]" class="form-control" placeholder="Job Title"></div>
            <div class="col-md-2"><input type="date" name="experiences[new_${expIndex}][from_date]" class="form-control"></div>
            <div class="col-md-2"><input type="date" name="experiences[new_${expIndex}][to_date]" class="form-control"></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
        </div>`);
        expIndex++;
    });

    $(document).on('click', '.remove-row', function(){ $(this).closest('.row').remove(); });

    // User Account Toggle
    $('#create_user_account').on('change', function() {
        if(this.checked) {
            $('#user-account-fields').slideDown();
        } else {
            $('#user-account-fields').slideUp();
        }
    });
    if($('#create_user_account').is(':checked')) {
        $('#user-account-fields').show();
    }

    // ✅ Tax Calculator JS (New Logic: Scrape DOM inputs + Joining Date)
    function openTaxPopup() {
        let basic = unformatNumber($('#basic_salary').val());
        let name = $('#name').val() || 'New Employee';
        
        // ✅ Get joining date value
        let joinDate = $('#joining_date').val();

        let params = new URLSearchParams();
        params.append('basic', basic);
        params.append('name', name);
        params.append('popup', 1);
        
        if (joinDate) {
            params.append('joining_date', joinDate);
        }

        $('.allowance').each(function() {
            let val = unformatNumber($(this).val());
            if(val > 0) {
                 let inputName = $(this).attr('name'); // components[4]
                 let idMatch = inputName.match(/\d+/);
                 if(idMatch) {
                     params.append(`components[${idMatch[0]}]`, val);
                 }
            }
        });

        const url = "{{ route('tools.taxCalculator') }}?" + params.toString();
        const w = 1100, h = 760;
        const y = window.top.outerHeight / 2 + window.top.screenY - ( h / 2);
        const x = window.top.outerWidth  / 2 + window.top.screenX - ( w / 2);
        window.open(url, 'TaxCalculator', `width=${w},height=${h},left=${x},top=${y},resizable=yes,scrollbars=yes`);
    }
    // Only attach click handler if button exists (Create mode)
    $(document).on('click', '#btn-open-tax-calc', openTaxPopup);

    // Before submit
    $(document).on('submit', 'form', function(){
        const toRaw = (sel)=> $(sel).each(function(){ $(this).val(unformatNumber($(this).val())); });
        toRaw('#basic_salary');
        toRaw('.allowance');
        toRaw('.deduction');
        toRaw('#gross_salary');
        toRaw('#net_salary');
    });
});
</script>
@endpush