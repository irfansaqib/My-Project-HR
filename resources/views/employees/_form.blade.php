<div class="modal fade" id="addDesignationModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Designation</h5></div><div class="modal-body"><input type="text" class="form-control" id="new_designation_name" placeholder="Designation Name"><div id="designation-error" class="text-danger mt-2 d-none"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="saveDesignationBtn">Save</button></div></div></div>
</div>
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Department</h5></div><div class="modal-body"><input type="text" class="form-control" id="new_department_name" placeholder="Department Name"><div id="department-error" class="text-danger mt-2 d-none"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="saveDepartmentBtn">Save</button></div></div></div>
</div>

<div class="card-body">
    <div class="row">
        <div class="col-md-9">
            <h5 class="mb-3">Personal Information</h5>
            <div class="row">
                <div class="col-md-6 form-group"><label for="name">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" id="name" value="{{ old('name', $employee->name ?? '') }}" required>@error('name') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
                <div class="col-md-6 form-group"><label for="father_name">Father's Name</label><input type="text" name="father_name" class="form-control" id="father_name" value="{{ old('father_name', $employee->father_name ?? '') }}">@error('father_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group"><label for="cnic">CNIC <span class="text-danger">*</span></label><input type="text" name="cnic" class="form-control" id="cnic" value="{{ old('cnic', $employee->cnic ?? '') }}" required placeholder="00000-0000000-0">@error('cnic') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
                <div class="col-md-6 form-group"><label for="dob">Date of Birth</label><input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob', $employee->dob ?? '') }}">@error('dob') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
            </div>
            <div class="row">
                 <div class="col-md-12 form-group"><label for="gender">Gender</label><select name="gender" id="gender" class="form-control"><option value="">Select Gender</option><option value="Male" @if(old('gender', $employee->gender ?? '') == 'Male') selected @endif>Male</option><option value="Female" @if(old('gender', $employee->gender ?? '') == 'Female') selected @endif>Female</option></select>@error('gender') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <label>Employee Photo</label>
            <div class="mb-2"><img id="photo-preview" src="{{ isset($employee) && $employee->photo_path ? asset('storage/' . $employee->photo_path) : 'https://via.placeholder.com/150' }}" alt="Photo Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;"></div>
            <div class="custom-file"><input type="file" name="photo" class="custom-file-input" id="photo" accept="image/*"><label class="custom-file-label" for="photo">Choose photo</label></div>
            @error('photo') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr><h5 class="mt-4 mb-3">Contact Details</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="phone">Contact Number <span class="text-danger">*</span></label><input type="text" name="phone" class="form-control" id="phone" value="{{ old('phone', $employee->phone ?? '') }}" required>@error('phone') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-6 form-group"><label for="email">Email Address <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" id="email" value="{{ old('email', $employee->email ?? '') }}" required>@error('email') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
    </div>
    <div class="form-group"><label for="address">Address</label><textarea name="address" class="form-control" id="address" rows="3">{{ old('address', $employee->address ?? '') }}</textarea>@error('address') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>

    <hr><h5 class="mt-4 mb-3">Emergency Contact Details</h5>
    <div class="row">
        <div class="col-md-4 form-group"><label for="emergency_contact_name">Contact Person Name</label><input type="text" name="emergency_contact_name" class="form-control" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">@error('emergency_contact_name') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-4 form-group"><label for="emergency_contact_relation">Relation</label><input type="text" name="emergency_contact_relation" class="form-control" id="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation ?? '') }}">@error('emergency_contact_relation') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-4 form-group"><label for="emergency_contact_phone">Contact Number</label><input type="text" name="emergency_contact_phone" class="form-control" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}">@error('emergency_contact_phone') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
    </div>

    <hr><h5 class="mt-4 mb-3">Employment Details</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="designation">Designation <span class="text-danger">*</span></label><div class="input-group"><select name="designation" id="designation" class="form-control" required><option value="">Select a Designation</option>@foreach($designations as $designation)<option value="{{ $designation->name }}" @if(old('designation', $employee->designation ?? '') == $designation->name) selected @endif>{{ $designation->name }}</option>@endforeach</select><div class="input-group-append"><button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDesignationModal">+</button></div></div>@error('designation') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-6 form-group"><label for="department">Department</label><div class="input-group"><select name="department" id="department" class="form-control"><option value="">Select a Department</option>@foreach($departments as $department)<option value="{{ $department->name }}" @if(old('department', $employee->department ?? '') == $department->name) selected @endif>{{ $department->name }}</option>@endforeach</select><div class="input-group-append"><button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDepartmentModal">+</button></div></div>@error('department') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group"><label for="joining_date">Date of Joining</label><input type="date" name="joining_date" class="form-control" id="joining_date" value="{{ old('joining_date', $employee->joining_date ?? '') }}">@error('joining_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-4 form-group"><label for="status">Status</label><select name="status" id="status" class="form-control"><option value="active" @if(old('status', $employee->status ?? 'active') == 'active') selected @endif>Active</option><option value="resigned" @if(old('status', $employee->status ?? '') == 'resigned') selected @endif>Resigned</option><option value="terminated" @if(old('status', $employee->status ?? '') == 'terminated') selected @endif>Terminated</option></select>@error('status') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-4 form-group"><label for="probation_period">Probation Period (Months)</label><input type="number" name="probation_period" class="form-control" id="probation_period" value="{{ old('probation_period', $employee->probation_period ?? 3) }}">@error('probation_period') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
    </div>
    <div class="form-group">
        <label for="job_description">Job Description / Responsibilities</label>
        <textarea name="job_description" class="form-control" id="job_description" rows="4">{{ old('job_description', $employee->job_description ?? '') }}</textarea>
        @error('job_description') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>

    <hr><h5 class="mt-4 mb-3">Qualifications</h5>
    <div id="qualifications-wrapper">
        @if(isset($employee) && $employee->qualifications->isNotEmpty())
            @foreach($employee->qualifications as $index => $qualification)
                <div class="row mb-2">
                    <input type="hidden" name="qualifications[{{ $index }}][id]" value="{{ $qualification->id }}">
                    <div class="col-md-4"><input type="text" name="qualifications[{{ $index }}][degree_title]" class="form-control" placeholder="Degree Title" value="{{ $qualification->degree_title }}" required></div>
                    <div class="col-md-4"><input type="text" name="qualifications[{{ $index }}][institute]" class="form-control" placeholder="Institute" value="{{ $qualification->institute }}" required></div>
                    <div class="col-md-3"><input type="number" name="qualifications[{{ $index }}][year_of_passing]" class="form-control" placeholder="Year" value="{{ $qualification->year_of_passing }}" required></div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-success mb-3" id="add-qualification">Add Qualification</button>

    <hr><h5 class="mt-4 mb-3">Work Experience</h5>
    <div id="experiences-wrapper">
        @if(isset($employee) && $employee->experiences->isNotEmpty())
            @foreach($employee->experiences as $index => $experience)
                <div class="row mb-2">
                    <input type="hidden" name="experiences[{{ $index }}][id]" value="{{ $experience->id }}">
                    <div class="col-md-3"><input type="text" name="experiences[{{ $index }}][company_name]" class="form-control" placeholder="Company" value="{{ $experience->company_name }}" required></div>
                    <div class="col-md-3"><input type="text" name="experiences[{{ $index }}][job_title]" class="form-control" placeholder="Job Title" value="{{ $experience->job_title }}" required></div>
                    <div class="col-md-2"><input type="date" name="experiences[{{ $index }}][from_date]" class="form-control" value="{{ $experience->from_date }}" required></div>
                    <div class="col-md-2"><input type="date" name="experiences[{{ $index }}][to_date]" class="form-control" value="{{ $experience->to_date }}" required></div>
                    <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-row">X</button></div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-success mb-3" id="add-experience">Add Experience</button>

    <hr><h5 class="mt-4 mb-3">Salary Details</h5>
    <div class="row">
        <div class="col-md-3 form-group"><label for="basic_salary">Basic Salary</label><input type="number" step="0.01" name="basic_salary" class="form-control salary-component" id="basic_salary" value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}"></div>
        <div class="col-md-3 form-group"><label for="house_rent">House Rent</label><input type="number" step="0.01" name="house_rent" class="form-control salary-component" id="house_rent" value="{{ old('house_rent', $employee->house_rent ?? 0) }}"></div>
        <div class="col-md-3 form-group"><label for="utilities">Utilities</label><input type="number" step="0.01" name="utilities" class="form-control salary-component" id="utilities" value="{{ old('utilities', $employee->utilities ?? 0) }}"></div>
        <div class="col-md-3 form-group"><label for="medical">Medical</label><input type="number" step="0.01" name="medical" class="form-control salary-component" id="medical" value="{{ old('medical', $employee->medical ?? 0) }}"></div>
    </div>
    <div class="row">
        <div class="col-md-3 form-group"><label for="conveyance">Conveyance</label><input type="number" step="0.01" name="conveyance" class="form-control salary-component" id="conveyance" value="{{ old('conveyance', $employee->conveyance ?? 0) }}"></div>
        <div class="col-md-3 form-group"><label for="other_allowance">Other Allowance</label><input type="number" step="0.01" name="other_allowance" class="form-control salary-component" id="other_allowance" value="{{ old('other_allowance', $employee->other_allowance ?? 0) }}"></div>
        <div class="col-md-6 form-group"><label for="total_salary">Total Salary</label><input type="number" class="form-control" id="total_salary" readonly></div>
    </div>

    <hr><h5 class="mt-4 mb-3">Annual Leaves Allocation</h5>
    <div class="row">
        <div class="col-md-6 form-group"><label for="leave_period_from">Leave Period From</label><input type="date" name="leave_period_from" class="form-control" id="leave_period_from" value="{{ old('leave_period_from', $employee->leave_period_from ?? '') }}"> @error('leave_period_from') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
        <div class="col-md-6 form-group"><label for="leave_period_to">Leave Period To</label><input type="date" name="leave_period_to" class="form-control" id="leave_period_to" value="{{ old('leave_period_to', $employee->leave_period_to ?? '') }}"> @error('leave_period_to') <div class="text-danger mt-1">{{ $message }}</div> @enderror</div>
    </div>
    <div class="row">
        <div class="col-md-2 form-group"><label for="leaves_sick">Sick</label><input type="number" name="leaves_sick" class="form-control leave-component" id="leaves_sick" value="{{ old('leaves_sick', $employee->leaves_sick ?? 0) }}"></div>
        <div class="col-md-2 form-group"><label for="leaves_casual">Casual</label><input type="number" name="leaves_casual" class="form-control leave-component" id="leaves_casual" value="{{ old('leaves_casual', $employee->leaves_casual ?? 0) }}"></div>
        <div class="col-md-2 form-group"><label for="leaves_annual">Annual</label><input type="number" name="leaves_annual" class="form-control leave-component" id="leaves_annual" value="{{ old('leaves_annual', $employee->leaves_annual ?? 0) }}"></div>
        <div class="col-md-2 form-group"><label for="leaves_other">Other</label><input type="number" name="leaves_other" class="form-control leave-component" id="leaves_other" value="{{ old('leaves_other', $employee->leaves_other ?? 0) }}"></div>
        <div class="col-md-4 form-group"><label for="total_leaves">Total Leaves</label><input type="number" class="form-control" id="total_leaves" readonly></div>
    </div>
    
    <hr><h5 class="mt-4 mb-3">Bank Account Details</h5>
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
    
    <hr><h5 class="mt-4 mb-3">Other Documents</h5>
    <div class="form-group">
        <label for="attachment">Attach Document (PDF, JPG, PNG)</label>
        <div class="custom-file">
            <input type="file" name="attachment" class="custom-file-input" id="attachment">
            <label class="custom-file-label" for="attachment">Choose file</label>
        </div>
        @if(isset($employee) && $employee->attachment_path)
        <div class="mt-2">Current file: <a href="{{ asset('storage/' . $employee->attachment_path) }}" target="_blank">View Document</a></div>
        @endif
        @error('attachment') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
        
        $(document).on('click', '.remove-row', function() {
            $(this).closest('.row').remove();
        });
        
        const salaryComponents = document.querySelectorAll('.salary-component');
        const totalSalaryInput = document.getElementById('total_salary');
        const leaveComponents = document.querySelectorAll('.leave-component');
        const totalLeavesInput = document.getElementById('total_leaves');
        function calculateTotal(components) {
            let total = 0;
            components.forEach(function (input) { total += parseFloat(input.value) || 0; });
            return total;
        }
        function updateTotals() {
            totalSalaryInput.value = calculateTotal(salaryComponents).toFixed(2);
            totalLeavesInput.value = calculateTotal(leaveComponents);
        }
        salaryComponents.forEach(input => input.addEventListener('input', updateTotals));
        leaveComponents.forEach(input => input.addEventListener('input', updateTotals));
        updateTotals();

        function saveNewItem(name, url, selectId, modalId, errorId, inputId) {
            let inputElement = document.getElementById(inputId);
            let errorDiv = document.getElementById(errorId);
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ name: name })
            })
            .then(response => {
                if (!response.ok) { return response.json().then(data => Promise.reject(data)); }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    let select = document.getElementById(selectId);
                    let newItem = data.designation || data.department;
                    let newOption = new Option(newItem.name, newItem.name, true, true);
                    select.add(newOption);
                    $(modalId).modal('hide');
                    inputElement.value = '';
                    errorDiv.classList.add('d-none');
                }
            })
            .catch(errorData => {
                if (errorData.errors && errorData.errors.name) {
                    errorDiv.textContent = errorData.errors.name[0];
                    errorDiv.classList.remove('d-none');
                }
            });
        }

        document.getElementById('saveDesignationBtn').addEventListener('click', function() {
            let name = document.getElementById('new_designation_name').value;
            saveNewItem(name, "{{ route('designations.store') }}", 'designation', '#addDesignationModal', 'designation-error', 'new_designation_name');
        });

        document.getElementById('saveDepartmentBtn').addEventListener('click', function() {
            let name = document.getElementById('new_department_name').value;
            saveNewItem(name, "{{ route('departments.store') }}", 'department', '#addDepartmentModal', 'department-error', 'new_department_name');
        });
    });
</script>
@endpush