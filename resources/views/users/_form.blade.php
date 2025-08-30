<div class="card-body">
    <div class="form-group">
        <label for="employee_id">Select Employee <span class="text-danger">*</span></label>
        <select name="employee_id" id="employee_id" class="form-control" required>
            <option value="">-- Select an Employee --</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" 
                        data-name="{{ $employee->name }}" 
                        data-email="{{ $employee->email }}"
                        {{ old('employee_id', $user->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->employee_number }} - {{ $employee->name }}
                </option>
            @endforeach
        </select>
        @error('employee_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
    
    <div class="form-group">
        <label for="name">User's Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $user->name ?? '') }}" required readonly>
        @error('name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label for="email">User's Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $user->email ?? '') }}" required readonly>
        @error('email') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>
    
    <div class="form-group">
        <label for="role">User Type <span class="text-danger">*</span></label>
        <select name="role" id="role" class="form-control" required>
            <option value="">-- Select a User Type --</option>
            @foreach($roles as $role)
                <option value="{{ $role }}" {{ isset($user) && $user->hasRole($role) ? 'selected' : '' }}>
                    {{ $role }}
                </option>
            @endforeach
        </select>
        @error('role') <div class="text-danger mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" id="password" {{ isset($user) ? '' : 'required' }}>
             @if(isset($user)) <small class="form-text text-muted">Leave blank to keep current password.</small> @endif
            @error('password') <div class="text-danger mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
        </div>
    </div>

    <hr>
    
    <div id="permissions-wrapper" class="d-none">
        <h5>Assign Permissions</h5>
        <p class="text-muted">These permissions only apply if the "User Type" is set to "User". Admins and Owners automatically have all permissions.</p>
        <div class="row">
        @php
            $icons = ['employee' => 'fas fa-user-tie', 'user' => 'fas fa-users-cog', 'customer' => 'fas fa-address-book', 'leave-request' => 'fas fa-calendar-alt', 'report' => 'fas fa-chart-bar'];
        @endphp
        @foreach($permissions as $module => $permissionGroup)
            <div class="col-md-4 col-sm-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="{{ $icons[$module] ?? 'fas fa-cogs' }} mr-2"></i>{{ ucfirst(str_replace('-', ' ', $module)) }}</h3>
                        <div class="card-tools">
                             <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input select-all" id="select-all-{{ $module }}" data-module="{{ $module }}">
                                <label class="custom-control-label" for="select-all-{{ $module }}">Select All</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($permissionGroup as $permission)
                        <div class="form-group clearfix">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input permission-checkbox" type="checkbox" name="permissions[]" 
                                       value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                       data-module="{{ $module }}"
                                       {{ (isset($user) && $user->hasPermissionTo($permission->name)) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="perm_{{ $permission->id }}">
                                    {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Logic for auto-filling name/email from employee select ---
        const employeeSelect = document.getElementById('employee_id');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        
        employeeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            nameInput.value = selectedOption.getAttribute('data-name') || '';
            emailInput.value = selectedOption.getAttribute('data-email') || '';
        });

        // --- Logic for showing/hiding the permissions block ---
        const roleSelect = document.getElementById('role');
        const permissionsWrapper = document.getElementById('permissions-wrapper');

        function togglePermissions() {
            if (roleSelect.value === 'User') {
                permissionsWrapper.classList.remove('d-none');
            } else {
                permissionsWrapper.classList.add('d-none');
            }
        }
        roleSelect.addEventListener('change', togglePermissions);
        togglePermissions(); // Initial check on page load

        // --- Logic for "Select All" functionality ---
        const selectAllCheckboxes = document.querySelectorAll('.select-all');
        selectAllCheckboxes.forEach(function(selectAll) {
            selectAll.addEventListener('click', function() {
                const module = this.getAttribute('data-module');
                const permissionCheckboxes = document.querySelectorAll('.permission-checkbox[data-module="' + module + '"]');
                permissionCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        });

        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        permissionCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('click', function() {
                const module = this.getAttribute('data-module');
                const allCheckboxesInModule = document.querySelectorAll('.permission-checkbox[data-module="' + module + '"]');
                const selectAllForModule = document.querySelector('.select-all[data-module="' + module + '"]');
                
                // If any checkbox in the module is unchecked, uncheck the "Select All"
                let allChecked = true;
                allCheckboxesInModule.forEach(function(cb) {
                    if (!cb.checked) {
                        allChecked = false;
                    }
                });
                selectAllForModule.checked = allChecked;
            });
        });
    });
</script>
@endpush