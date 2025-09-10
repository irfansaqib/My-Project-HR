<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                <button type="button" class="btn btn-primary" id="saveDesignationBtn">Save Designation</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                <button type="button" class="btn btn-primary" id="saveDepartmentBtn">Save Department</button>
            </div>
        </div>
    </div>
</div>

<div class="card-body">
    {{-- Personal Information --}}
    <div class="row">
        <div class="col-md-9">
            <h5 class="mb-3">Personal Information</h5>
            <div class="row">
                <div class="col-md-6 form-group"><label for="name">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" id="name" value="{{ old('name', $employee->name ?? '') }}" required></div>
                <div class="col-md-6 form-group"><label for="father_name">Father's Name</label><input type="text" name="father_name" class="form-control" id="father_name" value="{{ old('father_name', $employee->father_name ?? '') }}"></div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group"><label for="cnic">CNIC <span class="text-danger">*</span></label><input type="text" name="cnic" class="form-control" id="cnic" value="{{ old('cnic', $employee->cnic ?? '') }}" required placeholder="00000-0000000-0"></div>
                <div class="col-md-6 form-group"><label for="dob">Date of Birth</label><input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob', $employee->dob ?? '') }}"></div>
            </div>
            <div class="row">
                 <div class="col-md-12 form-group"><label for="gender">Gender</label><select name="gender" id="gender" class="form-control"><option value="">Select Gender</option><option value="Male" @if(old('gender', $employee->gender ?? '') == 'Male') selected @endif>Male</option><option value="Female" @if(old('gender', $employee->gender ?? '') == 'Female') selected @endif>Female</option></select></div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <label>Employee Photo</label>
            <div class="mb-2"><img id="photo-preview" src="{{ isset($employee) && $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/150' }}" alt="Photo Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;"></div>
            <div class="custom-file"><input type="file" name="photo" class="custom-file-input" id="photo" accept="image/*"><label class="custom-file-label" for="photo">Choose photo</label></div>
        </div>
    </div>

    {{-- Contact Details --}}
    <hr><h5 class="mt-4 mb-3">Contact Details</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="phone">Contact Number <span class="text-danger">*</span></label><input type="text" name="phone" class="form-control" id="phone" value="{{ old('phone', $employee->phone ?? '') }}" required></div>
        <div class="col-md-6 form-group"><label for="email">Email Address <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" id="email" value="{{ old('email', $employee->email ?? '') }}" required></div>
    </div>
    <div class="form-group"><label for="address">Address</label><textarea name="address" class="form-control" id="address" rows="3">{{ old('address', $employee->address ?? '') }}</textarea></div>

    {{-- Emergency Contact Details --}}
    <hr><h5 class="mt-4 mb-3">Emergency Contact Details</h5>
    <div class="row">
        <div class="col-md-4 form-group"><label for="emergency_contact_name">Contact Person Name</label><input type="text" name="emergency_contact_name" class="form-control" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}"></div>
        <div class="col-md-4 form-group"><label for="emergency_contact_relation">Relation</label><input type="text" name="emergency_contact_relation" class="form-control" id="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation ?? '') }}"></div>
        <div class="col-md-4 form-group"><label for="emergency_contact_phone">Contact Number</label><input type="text" name="emergency_contact_phone" class="form-control" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}"></div>
    </div>

    {{-- Employment Details --}}
    <hr><h5 class="mt-4 mb-3">Employment Details</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="designation">Designation <span class="text-danger">*</span></label><div class="input-group"><select name="designation" id="designation" class="form-control" required><option value="">Select a Designation</option>@foreach($designations as $designation)<option value="{{ $designation->name }}" @if(old('designation', $employee->designation ?? '') == $designation->name) selected @endif>{{ $designation->name }}</option>@endforeach</select><div class="input-group-append"><button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDesignationModal">+</button></div></div></div>
        <div class="col-md-6 form-group"><label for="department">Department</label><div class="input-group"><select name="department" id="department" class="form-control"><option value="">Select a Department</option>@foreach($departments as $department)<option value="{{ $department->name }}" @if(old('department', $employee->department ?? '') == $department->name) selected @endif>{{ $department->name }}</option>@endforeach</select><div class="input-group-append"><button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDepartmentModal">+</button></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group"><label for="joining_date">Date of Joining</label><input type="date" name="joining_date" class="form-control" id="joining_date" value="{{ old('joining_date', $employee->joining_date ?? '') }}"></div>
        <div class="col-md-4 form-group"><label for="status">Status</label><select name="status" id="status" class="form-control"><option value="active" @if(old('status', $employee->status ?? 'active') == 'active') selected @endif>Active</option><option value="resigned" @if(old('status', $employee->status ?? '') == 'resigned') selected @endif>Resigned</option><option value="terminated" @if(old('status', $employee->status ?? '') == 'terminated') selected @endif>Terminated</option></select></div>
        <div class="col-md-4 form-group"><label for="probation_period">Probation Period (Months)</label><input type="number" name="probation_period" class="form-control" id="probation_period" value="{{ old('probation_period', $employee->probation_period ?? 3) }}"></div>
    </div>

    {{-- Salary Package --}}
    <hr><h5 class="mt-4 mb-3">Salary Package</h5>
    <div class="row">
        <div class="col-md-4 form-group">
            <label for="basic_salary">Basic Salary <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="basic_salary" class="form-control salary-calc" id="basic_salary" value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}" required>
        </div>
    </div>
    <div class="card card-outline card-success">
        <div class="card-header"><h3 class="card-title">Allowances</h3></div>
        <div class="card-body"><div class="row">@forelse ($allowances as $allowance)<div class="col-md-4 form-group"><label for="component_{{ $allowance->id }}">{{ $allowance->name }}</label><input type="number" step="0.01" name="components[{{ $allowance->id }}]" class="form-control salary-calc allowance" id="component_{{ $allowance->id }}" value="{{ old('components.'.$allowance->id, isset($employee) && $employee->salaryComponents->find($allowance->id) ? $employee->salaryComponents->find($allowance->id)->pivot->amount : 0) }}"></div>@empty<div class="col-12"><p class="text-muted">No allowance components defined.</p></div>@endforelse</div></div>
    </div>
    <div class="card card-outline card-danger mt-3">
        <div class="card-header"><h3 class="card-title">Deductions</h3></div>
        <div class="card-body"><div class="row">@forelse ($deductions as $deduction)<div class="col-md-4 form-group"><label for="component_{{ $deduction->id }}">{{ $deduction->name }}</label><input type="number" step="0.01" name="components[{{ $deduction->id }}]" class="form-control salary-calc deduction" id="component_{{ $deduction->id }}" value="{{ old('components.'.$deduction->id, isset($employee) && $employee->salaryComponents->find($deduction->id) ? $employee->salaryComponents->find($deduction->id)->pivot->amount : 0) }}"></div>@empty<div class="col-12"><p class="text-muted">No deduction components defined.</p></div>@endforelse</div></div>
    </div>
    <div class="row mt-3 bg-light pt-3 rounded">
        <div class="col-md-6 form-group">
            <label for="gross_salary">Gross Salary</label>
            <input type="text" name="gross_salary" class="form-control" id="gross_salary" value="{{ old('gross_salary', $employee->gross_salary ?? '0.00') }}" readonly style="font-weight: bold; background-color: #e9ecef;">
        </div>
        <div class="col-md-6 form-group">
            <label for="net_salary">Net Salary</label>
            <input type="text" name="net_salary" class="form-control" id="net_salary" value="{{ old('net_salary', $employee->net_salary ?? '0.00') }}" readonly style="font-weight: bold; background-color: #e9ecef;">
        </div>
    </div>
    
    {{-- Bank Account Details --}}
    <hr><h5 class="mt-4 mb-3">Bank Account Details</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="bank_account_title">Account Title</label><input type="text" name="bank_account_title" class="form-control" id="bank_account_title" value="{{ old('bank_account_title', $employee->bank_account_title ?? '') }}"></div>
        <div class="col-md-6 form-group"><label for="bank_account_number">Account Number</label><input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}"></div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group"><label for="bank_name">Bank Name</label><input type="text" name="bank_name" class="form-control" id="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}"></div>
        <div class="col-md-6 form-group"><label for="bank_branch">Branch Name & Code</label><input type="text" name="bank_branch" class="form-control" id="bank_branch" value="{{ old('bank_branch', $employee->bank_branch ?? '') }}"></div>
    </div>
    <div class="row">
        <div class="col-md-12 form-group"><label for="business_bank_account_id">Paying Bank Account (from Business)</label><select name="business_bank_account_id" id="business_bank_account_id" class="form-control"><option value="">-- Select a Bank Account --</option>@foreach($businessBankAccounts as $account)<option value="{{ $account->id }}" @if(old('business_bank_account_id', $employee->business_bank_account_id ?? '') == $account->id) selected @endif>{{ $account->bank_name }} - ({{ $account->account_number }})</option>@endforeach</select></div>
    </div>
    
    {{-- Leaves Details --}}
    <hr><h5 class="mt-4 mb-3">Leaves Details</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="leave_period_from">Leave Period From</label>
            <input type="date" name="leave_period_from" class="form-control" id="leave_period_from" value="{{ old('leave_period_from', $employee->leave_period_from ?? '') }}">
        </div>
        <div class="col-md-6 form-group">
            <label for="leave_period_to">Leave Period To</label>
            <input type="date" name="leave_period_to" class="form-control" id="leave_period_to" value="{{ old('leave_period_to', $employee->leave_period_to ?? '') }}">
        </div>
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
            <div class="col-12">
                <p class="text-muted">No leave types have been defined yet. Please add them in the <a href="{{ route('leave-types.index') }}">Leave Types</a> section.</p>
            </div>
        @endforelse
    </div>
    <div class="row bg-light pt-3 rounded mt-2">
        <div class="col-md-3 form-group">
            <label for="total_leaves">Total Leaves</label>
            <input type="text" class="form-control" id="total_leaves" readonly style="font-weight: bold; background-color: #e9ecef;">
        </div>
    </div>
    
    {{-- Other Documents and Script --}}
    <hr><h5 class="mt-4 mb-3">Other Documents</h5>
    <div class="form-group">
        <label for="attachment">Attach Document (PDF, JPG, PNG)</label>
        <div class="custom-file"><input type="file" name="attachment" class="custom-file-input" id="attachment"><label class="custom-file-label" for="attachment">Choose file</label></div>
        @if(isset($employee) && $employee->attachment_path)
        <div class="mt-2">Current file: <a href="{{ asset('storage/' . $employee->attachment_path) }}" target="_blank">View Document</a></div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        function calculateSalary() {
            let basic = parseFloat($('#basic_salary').val()) || 0;
            let totalAllowances = 0;
            let totalDeductions = 0;
            $('.allowance').each(function() { totalAllowances += parseFloat($(this).val()) || 0; });
            $('.deduction').each(function() { totalDeductions += parseFloat($(this).val()) || 0; });
            let gross = basic + totalAllowances;
            let net = gross - totalDeductions;
            $('#gross_salary').val(gross.toFixed(2));
            $('#net_salary').val(net.toFixed(2));
        }
        
        function calculateLeaves() {
            let total = 0;
            $('.leave-calc').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            $('#total_leaves').val(total);
        }

        // Attach event listeners
        $('.salary-calc').on('input', calculateSalary);
        $('.leave-calc').on('input', calculateLeaves);

        // Run calculations on page load
        calculateSalary();
        calculateLeaves();

        // Other scripts
        $('#cnic').mask('00000-0000000-0');
        
        $('.custom-file-input').on('change', function(event) {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
            if (this.id === 'photo' && event.target.files && event.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { $('#photo-preview').attr('src', e.target.result); }
                reader.readAsDataURL(event.target.files[0]);
            }
        });

        let qualIndex = {{ isset($employee) && $employee->qualifications ? $employee->qualifications->count() : 0 }};
        $('#add-qualification').on('click', function() {
            $('#qualifications-wrapper').append(`<div class="row mb-2"><div class="col-md-4"><input type="text" name="qualifications[new_${qualIndex}][degree_title]" class="form-control" placeholder="Degree Title" required></div><div class="col-md-4"><input type="text" name="qualifications[new_${qualIndex}][institute]" class="form-control" placeholder="Institute" required></div><div class="col-md-3"><input type="number" name="qualifications[new_${qualIndex}][year_of_passing]" class="form-control" placeholder="Year" required></div><div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div></div>`);
            qualIndex++;
        });

        let expIndex = {{ isset($employee) && $employee->experiences ? $employee->experiences->count() : 0 }};
        $('#add-experience').on('click', function() {
            $('#experiences-wrapper').append(`<div class="row mb-2"><div class="col-md-3"><input type="text" name="experiences[new_${expIndex}][company_name]" class="form-control" placeholder="Company" required></div><div class="col-md-3"><input type="text" name="experiences[new_${expIndex}][job_title]" class="form-control" placeholder="Job Title" required></div><div class="col-md-2"><input type="date" name="experiences[new_${expIndex}][from_date]" class="form-control" required></div><div class="col-md-2"><input type="date" name="experiences[new_${expIndex}][to_date]" class="form-control" required></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div></div>`);
            expIndex++;
        });
        
        $(document).on('click', '.remove-row', function() { $(this).closest('.row').remove(); });

        function saveNewItem(name, url, selectId, modalId, errorId, inputId) {
            fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ name: name }) })
            .then(response => { if (!response.ok) { return response.json().then(data => Promise.reject(data)); } return response.json(); })
            .then(data => { if (data.success) { let select = $('#' + selectId); let newItem = data.designation || data.department; select.append(new Option(newItem.name, newItem.name, true, true)); $(modalId).modal('hide'); $('#' + inputId).val(''); $('#' + errorId).addClass('d-none'); } })
            .catch(errorData => { if (errorData.errors && errorData.errors.name) { $('#' + errorId).text(errorData.errors.name[0]).removeClass('d-none'); } });
        }
        $('#saveDesignationBtn').on('click', function() { saveNewItem($('#new_designation_name').val(), "{{ route('designations.store') }}", 'designation', '#addDesignationModal', 'designation-error', 'new_designation_name'); });
        $('#saveDepartmentBtn').on('click', function() { saveNewItem($('#new_department_name').val(), "{{ route('departments.store') }}", 'department', '#addDepartmentModal', 'department-error', 'new_department_name'); });
    });
</script>
@endpush